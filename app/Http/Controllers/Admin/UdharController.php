<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Family;
use App\Models\Sale;
use App\Models\Warehouse;
use App\Services\CustomerPaymentService;
use App\Services\UdharService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UdharController extends Controller
{
    protected CustomerPaymentService $paymentService;
    protected UdharService $udharService;

    public function __construct(CustomerPaymentService $paymentService, UdharService $udharService)
    {
        $this->paymentService = $paymentService;
        $this->udharService = $udharService;
    }

    /**
     * Display udhar management page with tabs for Customers and Families
     */
    public function index(Request $request): View
    {
        $this->authorize('sales.view');

        $user = auth()->user();
        $activeTab = $request->input('tab', 'customers'); // Default to customers tab
        
        // Check if warehouse filter should be shown
        $multiWarehouseService = app(\App\Services\MultiWarehouseFeatureService::class);
        $showWarehouseFilter = $multiWarehouseService->shouldShowWarehouseFilter();

        // Build filters
        $filters = [
            'search' => $request->input('search'),
            'family_id' => $request->input('family_id'),
            'warehouse_id' => $request->input('warehouse_id'),
            'status' => $request->input('status'),
            'only_outstanding' => $request->boolean('only_outstanding', true),
        ];

        // If single warehouse, auto-use the default warehouse
        if (!$showWarehouseFilter && empty($filters['warehouse_id'])) {
            $defaultWarehouse = $multiWarehouseService->getDefaultActiveWarehouse();
            if ($defaultWarehouse) {
                $filters['warehouse_id'] = $defaultWarehouse->id;
            }
        }

        // Apply warehouse restrictions for non-super-admins
        if (!$user->isSuperAdmin() && empty($filters['warehouse_id'])) {
            $userWarehouses = $user->warehouses()->pluck('warehouses.id');
            if ($userWarehouses->isNotEmpty()) {
                $filters['warehouse_id'] = $userWarehouses->first();
            }
        }

        // Get data based on active tab
        if ($activeTab === 'families') {
            // Family accounts view
            $familiesCollection = $this->udharService->getFamilyUdharSummary($filters);
            
            // Paginate families (10 per page)
            $page = \Illuminate\Pagination\Paginator::resolveCurrentPage();
            $perPage = 10;
            $families = new \Illuminate\Pagination\LengthAwarePaginator(
                $familiesCollection->forPage($page, $perPage)->values(),
                $familiesCollection->count(),
                $perPage,
                $page,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
            );

            $totalUdhar = $familiesCollection->sum('outstanding');
            $totalSales = $familiesCollection->sum('total_sales');
            $totalPaid = $familiesCollection->sum('total_paid');
            $accountsCount = $familiesCollection->count();

            // Get filter options (only get warehouses if multi-warehouse is enabled)
            $familyOptions = Family::active()->orderBy('name')->get();
            $warehouses = $showWarehouseFilter ? (
                $user->isSuperAdmin()
                    ? Warehouse::active()->orderBy('name')->get()
                    : $user->warehouses()->where('status', 'active')->orderBy('name')->get()
            ) : collect();

            return view('admin.udhar.index', compact(
                'families',
                'familyOptions',
                'warehouses',
                'filters',
                'activeTab',
                'showWarehouseFilter',
                'totalUdhar',
                'totalSales',
                'totalPaid',
                'accountsCount'
            ));

        } else {
            // Individual customer accounts view (default)
            $customersCollection = $this->udharService->getIndividualUdharSummary($filters);
            
            // Paginate customers (10 per page)
            $page = \Illuminate\Pagination\Paginator::resolveCurrentPage();
            $perPage = 10;
            $customers = new \Illuminate\Pagination\LengthAwarePaginator(
                $customersCollection->forPage($page, $perPage)->values(),
                $customersCollection->count(),
                $perPage,
                $page,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
            );

            $totalUdhar = $customersCollection->sum('outstanding');
            $totalSales = $customersCollection->sum('total_sales');
            $totalPaid = $customersCollection->sum('total_paid');
            $accountsCount = $customersCollection->count();

            // Get filter options (only get warehouses if multi-warehouse is enabled)
            $familyOptions = Family::active()->orderBy('name')->get();
            $warehouses = $showWarehouseFilter ? (
                $user->isSuperAdmin()
                    ? Warehouse::active()->orderBy('name')->get()
                    : $user->warehouses()->where('status', 'active')->orderBy('name')->get()
            ) : collect();

            return view('admin.udhar.index', compact(
                'customers',
                'familyOptions',
                'warehouses',
                'filters',
                'activeTab',
                'showWarehouseFilter',
                'totalUdhar',
                'totalSales',
                'totalPaid',
                'accountsCount'
            ));
        }
    }

    /**
     * Show individual customer account details
     */
    public function showCustomer(Customer $customer): View
    {
        $this->authorize('sales.view');

        // Verify user has access to this customer's warehouse
        if (!auth()->user()->canAccessWarehouse($customer->warehouse_id)) {
            abort(403, 'You do not have permission to view this customer.');
        }

        $customer->load(['family', 'warehouse']);

        // Get individual account balance
        $individualAccount = $this->udharService->getCustomerIndividualBalance($customer->id);
        
        // Get individual account transactions
        $individualTransactions = $this->udharService->getCustomerIndividualTransactions($customer->id);

        // Get family sales created by this customer (for transparency)
        $familySales = Sale::where('customer_id', $customer->id)
            ->where('udhar_account_type', Sale::UDHAR_ACCOUNT_TYPE_FAMILY)
            ->confirmed()
            ->with(['family', 'customerPayments'])
            ->orderBy('sale_date', 'desc')
            ->get();

        // Group family sales by family_id
        $familyAccounts = [];
        foreach ($familySales as $sale) {
            $familyId = $sale->family_id;
            if (!isset($familyAccounts[$familyId])) {
                $familyAccounts[$familyId] = [
                    'family' => $sale->family,
                    'total_sales' => 0,
                    'total_paid' => 0,
                    'outstanding' => 0,
                    'sales' => [],
                ];
            }
            
            $saleOutstanding = $sale->current_remaining_udhar;
            $salePaid = $sale->paid_amount + $sale->customerPayments->sum('amount');
            
            $familyAccounts[$familyId]['total_sales'] += $sale->total_amount;
            $familyAccounts[$familyId]['total_paid'] += $salePaid;
            $familyAccounts[$familyId]['outstanding'] += $saleOutstanding;
            $familyAccounts[$familyId]['sales'][] = $sale;
        }

        return view('admin.udhar.show-customer', compact(
            'customer',
            'individualAccount',
            'individualTransactions',
            'familyAccounts'
        ));
    }

    /**
     * Show family account details
     */
    public function showFamily(Family $family): View
    {
        $this->authorize('sales.view');

        // Get family balance
        $familyAccount = $this->udharService->getFamilyBalance($family->id);
        
        // Get outstanding sales for manual allocation
        $outstandingSales = Sale::where('family_id', $family->id)
            ->where('udhar_account_type', Sale::UDHAR_ACCOUNT_TYPE_FAMILY)
            ->confirmed()
            ->whereRaw('total_amount - paid_amount > 0')
            ->with(['customer', 'customerPayments'])
            ->orderBy('sale_date', 'asc')
            ->get();
        
        $familyAccount['outstanding_sales'] = $outstandingSales;
        
        // Get family members
        $familyAccount['members'] = Customer::where('family_id', $family->id)
            ->orderBy('name')
            ->get();
        
        // Get family transactions
        $familyTransactions = $this->udharService->getFamilyTransactions($family->id);

        // Get aging breakdown
        $agingBreakdown = $this->udharService->getFamilyAgingUdhar($family->id);

        return view('admin.udhar.show-family', compact(
            'family',
            'familyAccount',
            'familyTransactions',
            'agingBreakdown'
        ));
    }

    /**
     * Legacy show method - redirects to showCustomer for backward compatibility
     */
    public function show(Customer $customer): View
    {
        return $this->showCustomer($customer);
    }

    /**
     * Receive cash payment (AJAX endpoint)
     */
    public function receivePayment(Request $request, Sale $sale)
    {
        \Log::debug('receivePayment called', ['sale_id' => $sale->id, 'request' => $request->all()]);
        
        if (!auth()->user()->canAccessWarehouse($sale->warehouse_id)) {
            \Log::error('Warehouse access denied');
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            \Log::debug('Creating payment', ['sale_id' => $sale->id, 'amount' => $validated['amount']]);
            $payment = $this->paymentService->receiveCashPayment($sale, (float) $validated['amount']);
            \Log::debug('Payment created', ['payment_id' => $payment->id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Payment received successfully',
                'payment' => [
                    'id' => $payment->id,
                    'amount' => (float) $payment->amount,
                    'remaining_udhar' => (float) $sale->refresh()->current_remaining_udhar,
                ],
                'account_info' => [
                    'account_type' => $sale->udhar_account_type,
                    'account_label' => $sale->isIndividualAccount() ? 'Individual Account' : 'Family Account',
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Payment creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get sale payment history (AJAX)
     */
    public function getSalePayments(Sale $sale)
    {
        $this->authorize('sales.view');

        $payments = $this->paymentService->getSalePaymentHistory($sale);

        return response()->json([
            'success' => true,
            'payments' => $payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => number_format($payment->amount, 2),
                    'payment_date' => $payment->payment_date->format('M d, Y'),
                    'payment_method' => $payment->payment_method_label,
                    'reference_number' => $payment->reference_number,
                    'notes' => $payment->notes,
                    'received_by' => $payment->receiver?->name ?? 'System',
                ];
            }),
            'sale' => [
                'invoice_number' => $sale->invoice_number,
                'total_amount' => number_format($sale->total_amount, 2),
                'initial_paid' => number_format($sale->paid_amount, 2),
                'additional_payments' => number_format($sale->total_additional_payments, 2),
                'remaining_udhar' => number_format($sale->current_remaining_udhar, 2),
                'payment_status' => $sale->current_payment_status,
                'account_type' => $sale->udhar_account_type,
                'account_label' => $sale->udhar_account_type_label,
            ],
        ]);
    }

    /**
     * Receive individual customer payment (AJAX)
     */
    public function receiveIndividualPayment(Request $request, Customer $customer)
    {
        $this->authorize('sales.create');

        if (!auth()->user()->canAccessWarehouse($customer->warehouse_id)) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $payment = $this->paymentService->receiveIndividualCashPayment(
                $customer,
                (float) $validated['amount'],
                $validated['payment_date'],
                $validated['reference'] ?? null,
                $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Cash payment of Rs. ' . number_format($validated['amount'], 2) . ' received successfully.',
                'payment' => [
                    'id' => $payment->id,
                    'amount' => (float) $payment->amount,
                    'payment_date' => $payment->payment_date->format('M d, Y'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Receive family payment (AJAX)
     */
    public function receiveFamilyPayment(Request $request, Family $family)
    {
        $this->authorize('sales.create');

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'allocation_type' => 'required|in:auto,manual',
            'allocation' => 'required_if:allocation_type,manual|array',
            'allocation.*.sale_id' => 'required_with:allocation|integer|exists:sales,id',
            'allocation.*.amount' => 'required_with:allocation|numeric|min:0.01',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $allocation = [];
            if ($validated['allocation_type'] === 'manual') {
                $allocation = $validated['allocation'];
            }

            $payments = $this->paymentService->receiveFamilyCashPayment(
                $family,
                (float) $validated['amount'],
                $validated['payment_date'],
                $allocation,
                $validated['reference'] ?? null,
                $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Family cash payment of Rs. ' . number_format($validated['amount'], 2) . ' received successfully.',
                'payments_count' => count($payments),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get Udhar statistics for dashboard (AJAX)
     */
    public function getStatistics()
    {
        $this->authorize('sales.view');

        $stats = $this->udharService->getUdharStatistics();

        return response()->json([
            'success' => true,
            'statistics' => $stats,
        ]);
    }
}
