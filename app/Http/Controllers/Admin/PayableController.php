<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePayablePaymentRequest;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Services\PayableService;
use App\Services\PurchasePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayableController extends Controller
{
    protected PayableService $payableService;
    protected PurchasePaymentService $paymentService;

    public function __construct(
        PayableService $payableService,
        PurchasePaymentService $paymentService
    ) {
        $this->payableService = $payableService;
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of suppliers with outstanding payables
     */
    public function index(Request $request): View
    {
        // Check permission
        if (!auth()->user()->hasPermission('payables.view')) {
            abort(403, 'Unauthorized action.');
        }

        // Build filters array
        $filters = [
            'search' => $request->filled('search') ? $request->search : null,
            'payable_min' => $request->filled('payable_min') ? (float)$request->payable_min : null,
            'payable_max' => $request->filled('payable_max') ? (float)$request->payable_max : null,
        ];

        // Get suppliers with payables using service
        $suppliersData = $this->payableService->getSuppliersWithPayables($filters);

        // Apply pagination manually
        $page = $request->get('page', 1);
        $perPage = 15;
        $paginatedSuppliers = new \Illuminate\Pagination\LengthAwarePaginator(
            $suppliersData->forPage($page, $perPage)->values(),
            $suppliersData->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        // Get dashboard statistics
        $stats = $this->payableService->getPayableStatistics();

        return view('admin.payables.index', [
            'suppliers' => $paginatedSuppliers,
            'stats' => $stats,
            'filters' => $filters,
        ]);
    }

    /**
     * Show supplier payables details
     */
    public function details(Supplier $supplier): View
    {
        if (!auth()->user()->hasPermission('payables.view')) {
            abort(403, 'Unauthorized action.');
        }

        // Get supplier summary
        $summary = $this->payableService->getSupplierPayableSummary($supplier->id);

        // Get outstanding purchases
        $outstandingPurchases = Purchase::where('supplier_id', $supplier->id)
            ->where('status', Purchase::STATUS_CONFIRMED)
            ->where(function ($q) {
                $q->where('payment_status', Purchase::PAYMENT_STATUS_UNPAID)
                  ->orWhere('payment_status', Purchase::PAYMENT_STATUS_PARTIAL);
            })
            ->with(['warehouse', 'payments'])
            ->orderBy('purchase_date', 'desc')
            ->get()
            ->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'purchase_number' => $purchase->purchase_number,
                    'purchase_date' => $purchase->purchase_date->format('Y-m-d'),
                    'warehouse' => $purchase->warehouse->name,
                    'total_amount' => (float)$purchase->total_amount,
                    'paid_amount' => (float)$purchase->paid_amount,
                    'payable_amount' => max(0, (float)$purchase->total_amount - (float)$purchase->paid_amount),
                    'payment_status' => $purchase->payment_status,
                    'payment_status_label' => $purchase->payment_status_label,
                    'payment_status_badge' => $purchase->payment_status_badge,
                ];
            });

        // Supplier totals
        $supplierTotals = [
            'name' => $supplier->name,
            'company_name' => $supplier->company_name,
            'phone' => $supplier->phone,
            'total_purchases' => Purchase::where('supplier_id', $supplier->id)
                ->where('status', Purchase::STATUS_CONFIRMED)
                ->count(),
            'total_purchase_amount' => Purchase::where('supplier_id', $supplier->id)
                ->where('status', Purchase::STATUS_CONFIRMED)
                ->sum('total_amount'),
            'total_paid_amount' => Purchase::where('supplier_id', $supplier->id)
                ->where('status', Purchase::STATUS_CONFIRMED)
                ->sum('paid_amount'),
            'total_payable' => $summary['total_payable'],
        ];

        return view('admin.payables.details', [
            'supplier' => $supplier,
            'supplierTotals' => $supplierTotals,
            'outstandingPurchases' => $outstandingPurchases,
            'summary' => $summary,
        ]);
    }

    /**
     * Show payment recording form
     */
    public function recordPaymentModal(Supplier $supplier, Purchase $purchase): View
    {
        if (!auth()->user()->hasPermission('payables.create')) {
            abort(403, 'Unauthorized action.');
        }

        if ($purchase->supplier_id !== $supplier->id) {
            abort(404);
        }

        $payableAmount = max(0, $purchase->total_amount - $purchase->paid_amount);

        return view('admin.payables.payment-modal', [
            'supplier' => $supplier,
            'purchase' => $purchase,
            'payableAmount' => $payableAmount,
            'paymentMethods' => \App\Models\PurchasePayment::$methods,
        ]);
    }

    /**
     * Record payment for supplier payable
     */
    public function recordPayment(Supplier $supplier, StorePayablePaymentRequest $request): RedirectResponse
    {
        if (!auth()->user()->hasPermission('payables.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $purchase = Purchase::findOrFail($request->purchase_id);

            // Verify purchase belongs to supplier
            if ($purchase->supplier_id !== $supplier->id) {
                return back()->with('error', 'Invalid purchase for this supplier.');
            }

            // Validate purchase is confirmed
            if (!$purchase->isConfirmed()) {
                return back()->with('error', 'Can only record payments for confirmed purchases.');
            }

            // Validate amount
            $payableAmount = $purchase->total_amount - $purchase->paid_amount;
            if ($request->amount > $payableAmount) {
                return back()->with('error', 'Payment amount cannot exceed outstanding payable of Rs. ' . number_format($payableAmount, 2));
            }

            // Record payment using PaymentService
            $payment = $this->paymentService->recordPayment(
                purchaseId: $purchase->id,
                amount: (float)$request->amount,
                paymentMethod: $request->payment_method,
                paymentDate: $request->payment_date,
                referenceNumber: $request->reference_number,
                notes: $request->notes
            );

            // Refresh purchase to get updated amounts
            $purchase->refresh();

            $message = "Payment recorded successfully. ";
            if ($purchase->isPaid()) {
                $message .= "Purchase #{$purchase->purchase_number} is now fully paid.";
            } else {
                $message .= "Outstanding payable: Rs. " . number_format($payableAmount - $request->amount, 2);
            }

            return redirect()->route('admin.payables.details', $supplier)
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Error recording payment: ' . $e->getMessage());
        }
    }

    /**
     * Show supplier payable ledger
     */
    public function ledger(Supplier $supplier, Request $request): View
    {
        if (!auth()->user()->hasPermission('payables.view')) {
            abort(403, 'Unauthorized action.');
        }

        // Get all ledger entries for supplier
        $query = SupplierLedger::where('supplier_id', $supplier->id)
            ->with(['purchase', 'purchasePayment', 'creator'])
            ->orderBy('date', 'asc');

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $ledgerEntries = $query->paginate(25)->withQueryString();

        // Prepare ledger data
        $ledgerData = $ledgerEntries->getCollection()->map(function ($entry) {
            return [
                'id' => $entry->id,
                'date' => $entry->date->format('Y-m-d'),
                'type' => $entry->type,
                'type_label' => $entry->type_label,
                'type_badge' => $entry->type_badge,
                'description' => $entry->description,
                'reference_number' => $entry->reference_number,
                'purchase_number' => $entry->purchase ? $entry->purchase->purchase_number : 'N/A',
                'payable_added' => (float)$entry->payable_added,
                'payment_made' => (float)$entry->payment_made,
                'balance' => (float)$entry->balance,
            ];
        });

        // Get current balance
        $lastEntry = SupplierLedger::where('supplier_id', $supplier->id)
            ->orderBy('date', 'desc')
            ->first();
        $currentBalance = $lastEntry ? max(0, (float)$lastEntry->balance) : 0;

        return view('admin.payables.ledger', [
            'supplier' => $supplier,
            'ledgerEntries' => $ledgerData,
            'currentBalance' => $currentBalance,
            'paginator' => $ledgerEntries,
        ]);
    }

    /**
     * Show payable aging report
     */
    public function aging(Request $request): View
    {
        if (!auth()->user()->hasPermission('payables.view')) {
            abort(403, 'Unauthorized action.');
        }

        $supplierId = $request->get('supplier_id');
        $agingData = $this->payableService->getAgingPayables($supplierId ? (int)$supplierId : null);

        // Get suppliers list for filter
        $suppliers = Supplier::active()
            ->whereHas('purchases', function ($q) {
                $q->where('status', Purchase::STATUS_CONFIRMED)
                  ->where(function ($q) {
                      $q->where('payment_status', Purchase::PAYMENT_STATUS_UNPAID)
                        ->orWhere('payment_status', Purchase::PAYMENT_STATUS_PARTIAL);
                  });
            })
            ->orderBy('name')
            ->get();

        return view('admin.payables.aging', [
            'agingData' => $agingData,
            'suppliers' => $suppliers,
            'selectedSupplierId' => $supplierId,
        ]);
    }
}
