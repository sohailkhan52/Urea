<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreUdharPaymentRequest;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\CustomerLedger;
use App\Services\PaymentService;
use App\Services\UdharService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class UdharController extends Controller
{
    protected UdharService $udharService;
    protected PaymentService $paymentService;

    public function __construct(UdharService $udharService, PaymentService $paymentService)
    {
        $this->udharService = $udharService;
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of customers with outstanding Udhar.
     */
    public function index(Request $request): View
    {
        $this->authorize('udhar.view');

        $query = Customer::with(['sales' => function ($q) {
            $q->where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID)
              ->where('udhar_amount', '>', 0);
        }]);

        // Search by name or phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by customer type
        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        // Get all customers and manually filter those with udhar
        $allCustomers = $query->get();
        
        // Filter customers with outstanding udhar
        $customers = $allCustomers->filter(function ($customer) {
            $totalUdhar = Sale::where('customer_id', $customer->id)
                ->where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID)
                ->sum('udhar_amount');
            return $totalUdhar > 0;
        });

        // Apply minimum/maximum udhar filters
        if ($request->filled('udhar_min')) {
            $minUdhar = (float) $request->udhar_min;
            $customers = $customers->filter(function ($customer) use ($minUdhar) {
                $totalUdhar = Sale::where('customer_id', $customer->id)
                    ->where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID)
                    ->sum('udhar_amount');
                return $totalUdhar >= $minUdhar;
            });
        }

        if ($request->filled('udhar_max')) {
            $maxUdhar = (float) $request->udhar_max;
            $customers = $customers->filter(function ($customer) use ($maxUdhar) {
                $totalUdhar = Sale::where('customer_id', $customer->id)
                    ->where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID)
                    ->sum('udhar_amount');
                return $totalUdhar <= $maxUdhar;
            });
        }

        // Date range filter
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $dateFrom = $request->date_from;
            $dateTo = $request->date_to;
            $customers = $customers->filter(function ($customer) use ($dateFrom, $dateTo) {
                $lastPayment = Payment::where('customer_id', $customer->id)
                    ->where('payment_status', Payment::STATUS_RECEIVED)
                    ->orderBy('payment_date', 'desc')
                    ->first();
                
                if (!$lastPayment) {
                    return false;
                }
                
                return $lastPayment->payment_date->between($dateFrom, $dateTo);
            });
        }

        // Prepare customer data with calculated totals
        $customersData = $customers->map(function ($customer) {
            $sales = Sale::where('customer_id', $customer->id)
                ->where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID)
                ->get();

            $totalSales = Sale::where('customer_id', $customer->id)
                ->where('status', Sale::STATUS_CONFIRMED)
                ->count();
            
            $totalAmount = Sale::where('customer_id', $customer->id)
                ->where('status', Sale::STATUS_CONFIRMED)
                ->sum('total_amount');
            
            $totalPaid = Sale::where('customer_id', $customer->id)
                ->where('status', Sale::STATUS_CONFIRMED)
                ->sum('paid_amount');
            
            $totalUdhar = $sales->sum('udhar_amount');
            
            $lastPayment = Payment::where('customer_id', $customer->id)
                ->where('payment_status', Payment::STATUS_RECEIVED)
                ->orderBy('payment_date', 'desc')
                ->first();

            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'customer_type' => $customer->customer_type,
                'customer_type_label' => $customer->type_label,
                'type_badge' => $customer->type_badge,
                'total_sales' => $totalSales,
                'total_amount' => $totalAmount,
                'total_paid' => $totalPaid,
                'total_udhar' => $totalUdhar,
                'last_payment_date' => $lastPayment ? $lastPayment->payment_date->format('Y-m-d') : 'N/A',
                'status' => $customer->status,
                'status_label' => $customer->status_label,
                'status_badge' => $customer->status_badge,
            ];
        })->sortByDesc('total_udhar');

        // Paginate the collection manually
        $page = $request->get('page', 1);
        $perPage = 15;
        
        // Calculate totals before pagination
        $totalCustomersWithUdhar = $customersData->count();
        $grandTotalUdhar = $customersData->sum('total_udhar');
        $partialInvoices = Sale::where('payment_status', Sale::PAYMENT_STATUS_PARTIAL)
            ->where('udhar_amount', '>', 0)
            ->count();
        $unpaidInvoices = Sale::where('payment_status', Sale::PAYMENT_STATUS_UNPAID)
            ->where('udhar_amount', '>', 0)
            ->count();

        // Convert collection to LengthAwarePaginator
        $paginatedCustomers = new \Illuminate\Pagination\LengthAwarePaginator(
            $customersData->forPage($page, $perPage)->values(),
            $customersData->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.udhar.index', [
            'customers' => $paginatedCustomers,
            'totalCustomersWithUdhar' => $totalCustomersWithUdhar,
            'grandTotalUdhar' => $grandTotalUdhar,
            'partialInvoices' => $partialInvoices,
            'unpaidInvoices' => $unpaidInvoices,
        ]);
    }

    /**
     * Show customer Udhar details with outstanding invoices.
     */
    public function details(Customer $customer): View
    {
        $this->authorize('udhar.view');

        // Get customer summary
        $summary = $this->udharService->getCustomerUdharSummary($customer->id);

        // Get outstanding sales
        $outstandingSales = Sale::where('customer_id', $customer->id)
            ->where('payment_status', '!=', Sale::PAYMENT_STATUS_PAID)
            ->with(['warehouse', 'payments'])
            ->orderBy('sale_date', 'desc')
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'sale_date' => $sale->sale_date->format('Y-m-d'),
                    'warehouse' => $sale->warehouse->name,
                    'total_amount' => $sale->total_amount,
                    'paid_amount' => $sale->paid_amount,
                    'due_amount' => $sale->due_amount,
                    'udhar_amount' => $sale->udhar_amount,
                    'payment_status' => $sale->payment_status,
                    'status' => $sale->status,
                ];
            });

        // Customer totals
        $customerTotals = [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'customer_type' => $customer->customer_type,
            'type_label' => $customer->type_label,
            'total_sales' => Sale::where('customer_id', $customer->id)
                ->where('status', Sale::STATUS_CONFIRMED)
                ->count(),
            'total_amount' => Sale::where('customer_id', $customer->id)
                ->where('status', Sale::STATUS_CONFIRMED)
                ->sum('total_amount'),
            'total_paid' => Sale::where('customer_id', $customer->id)
                ->where('status', Sale::STATUS_CONFIRMED)
                ->sum('paid_amount'),
            'total_udhar' => $summary['total_udhar'],
        ];

        return view('admin.udhar.details', [
            'customer' => $customer,
            'customerTotals' => $customerTotals,
            'outstandingSales' => $outstandingSales,
            'summary' => $summary,
        ]);
    }

    /**
     * Show record payment modal/form.
     */
    public function recordPaymentModal(Customer $customer, Sale $sale): View
    {
        $this->authorize('udhar.create');

        if ($sale->customer_id !== $customer->id) {
            abort(404);
        }

        return view('admin.udhar.payment-modal', [
            'customer' => $customer,
            'sale' => $sale,
            'outstandingAmount' => $sale->due_amount,
            'paymentMethods' => Payment::$methods,
        ]);
    }

    /**
     * Record payment for outstanding Udhar.
     */
    public function recordPayment(Customer $customer, StoreUdharPaymentRequest $request): RedirectResponse
    {
        $this->authorize('udhar.create');

        try {
            $sale = Sale::findOrFail($request->sale_id);

            // Verify sale belongs to customer
            if ($sale->customer_id !== $customer->id) {
                return back()->with('error', 'Invalid sale for this customer.');
            }

            // Validate amount
            if ($request->amount > $sale->due_amount) {
                return back()->with('error', 'Payment amount cannot exceed outstanding balance of Rs. ' . number_format($sale->due_amount, 2));
            }

            // Record payment using PaymentService
            $payment = $this->paymentService->recordPayment(
                saleId: $sale->id,
                amount: (float) $request->amount,
                paymentMethod: $request->payment_method,
                paymentDate: $request->payment_date,
                referenceNumber: $request->reference_number,
                notes: $request->notes
            );

            // Refresh sale to get updated amounts
            $sale->refresh();

            $message = "Payment recorded successfully. ";
            if ($sale->isPaid()) {
                $message .= "Invoice #{$sale->invoice_number} is now fully paid.";
            } else {
                $message .= "Outstanding balance: Rs. " . number_format($sale->due_amount, 2);
            }

            return redirect()->route('admin.udhar.details', $customer)
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Error recording payment: ' . $e->getMessage());
        }
    }

    /**
     * Show customer Udhar Ledger.
     */
    public function ledger(Customer $customer, Request $request): View
    {
        $this->authorize('udhar.view');

        // Get all ledger entries for customer
        $query = CustomerLedger::where('customer_id', $customer->id)
            ->with(['sale', 'payment', 'creator'])
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
                'invoice_number' => $entry->sale ? $entry->sale->invoice_number : ($entry->payment ? 'Payment #' . $entry->payment->id : 'N/A'),
                'debit' => $entry->debit,
                'credit' => $entry->credit,
                'balance' => $entry->balance,
                'reference_number' => $entry->reference_number,
            ];
        });

        // Get current balance
        $lastEntry = CustomerLedger::where('customer_id', $customer->id)
            ->orderBy('date', 'desc')
            ->first();
        $currentBalance = $lastEntry ? $lastEntry->balance : 0;

        return view('admin.udhar.ledger', [
            'customer' => $customer,
            'ledgerEntries' => $ledgerData,
            'currentBalance' => $currentBalance,
            'paginator' => $ledgerEntries,
        ]);
    }

    /**
     * Show complete transaction history for customer Udhar.
     */
    public function transactionHistory(Customer $customer, Request $request): View
    {
        $this->authorize('udhar.view');

        $udharHistoryService = new \App\Services\UdharHistoryService();

        // Get summary
        $summary = $udharHistoryService->getHistorySummary($customer->id);

        // Get filtered transactions
        $query = \App\Models\UdharHistory::where('customer_id', $customer->id)
            ->with(['sale', 'payment', 'creator']);

        // Filter by transaction type
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.udhar.transaction-history', [
            'customer' => $customer,
            'transactions' => $transactions,
            'summary' => $summary,
        ]);
    }

    /**
     * Print customer Udhar statement.
     */
    public function printStatement(Customer $customer, Request $request): View
    {
        $this->authorize('udhar.view');

        // Get all ledger entries for customer
        $query = CustomerLedger::where('customer_id', $customer->id)
            ->with(['sale', 'payment', 'creator'])
            ->orderBy('date', 'asc');

        // Filter by date range if provided
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $ledgerEntries = $query->get();

        // Prepare ledger data
        $ledgerData = $ledgerEntries->map(function ($entry) {
            return [
                'id' => $entry->id,
                'date' => $entry->date->format('Y-m-d'),
                'type' => $entry->type,
                'type_label' => $entry->type_label,
                'description' => $entry->description,
                'invoice_number' => $entry->sale ? $entry->sale->invoice_number : ($entry->payment ? 'Payment #' . $entry->payment->id : 'N/A'),
                'debit' => $entry->debit,
                'credit' => $entry->credit,
                'balance' => $entry->balance,
                'reference_number' => $entry->reference_number,
            ];
        });

        // Get current balance
        // If date filters are applied, use filtered results. Otherwise, get total udhar balance from customer
        if ($request->filled('date_from') || $request->filled('date_to')) {
            // Use last entry from filtered results
            $currentBalance = $ledgerEntries->isNotEmpty() ? $ledgerEntries->last()->balance : 0;
        } else {
            // Get total udhar from customer summary (all-time balance)
            $summary = $this->udharService->getCustomerUdharSummary($customer->id);
            $currentBalance = $summary['total_udhar'] ?? 0;
        }

        return view('admin.udhar.print-statement', [
            'customer' => $customer,
            'ledgerEntries' => $ledgerData,
            'currentBalance' => $currentBalance,
        ]);
    }
}
