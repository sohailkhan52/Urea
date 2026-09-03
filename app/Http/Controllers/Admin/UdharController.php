<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Family;
use App\Models\Sale;
use App\Models\Warehouse;
use App\Services\CustomerPaymentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UdharController extends Controller
{
    protected CustomerPaymentService $paymentService;

    public function __construct(CustomerPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display udhar management page
     */
    public function index(Request $request): View
    {
        $this->authorize('sales.view');

        $user = auth()->user();

        // Build filters
        $filters = [
            'search' => $request->input('search'),
            'family_id' => $request->input('family_id'),
            'warehouse_id' => $request->input('warehouse_id'),
            'status' => $request->input('status'),
            'only_outstanding' => $request->boolean('only_outstanding', true), // Default: show only with udhar
        ];

        // Apply warehouse restrictions for non-super-admins
        if (!$user->isSuperAdmin() && empty($filters['warehouse_id'])) {
            $userWarehouses = $user->warehouses()->pluck('warehouses.id');
            if ($userWarehouses->isNotEmpty()) {
                $filters['warehouse_id'] = $userWarehouses->first();
            }
        }

        // Get udhar summary
        $customersWithUdhar = $this->paymentService->getUdharSummary($filters);

        // Get filter options
        $families = Family::active()->orderBy('name')->get();
        
        $warehouses = $user->isSuperAdmin()
            ? Warehouse::active()->orderBy('name')->get()
            : $user->warehouses()->where('status', 'active')->orderBy('name')->get();

        // Calculate totals
        $totalUdhar = $customersWithUdhar->sum('current_udhar');
        $totalSales = $customersWithUdhar->sum('total_sales');
        $totalPaid = $customersWithUdhar->sum('total_paid');
        $customersCount = $customersWithUdhar->count();

        return view('admin.udhar.index', compact(
            'customersWithUdhar',
            'families',
            'warehouses',
            'filters',
            'totalUdhar',
            'totalSales',
            'totalPaid',
            'customersCount'
        ));
    }

    /**
     * Show customer account details
     */
    public function show(Customer $customer): View
    {
        $this->authorize('sales.view');

        // Verify user has access to this customer's warehouse
        if (!auth()->user()->canAccessWarehouse($customer->warehouse_id)) {
            abort(403, 'You do not have permission to view this customer.');
        }

        $customer->load(['family', 'warehouse']);

        // Get account statement
        $statement = $this->paymentService->getCustomerAccountStatement($customer);

        // Get all sales with payment status
        $sales = $customer->sales()
            ->confirmed()
            ->with(['customerPayments'])
            ->orderBy('sale_date', 'desc')
            ->get()
            ->map(function ($sale) {
                return [
                    'sale' => $sale,
                    'remaining_udhar' => $sale->current_remaining_udhar,
                    'payment_status' => $sale->current_payment_status,
                    'payments_count' => $sale->customerPayments->count(),
                ];
            });

        return view('admin.udhar.show', compact('customer', 'statement', 'sales'));
    }

    /**
     * Test endpoint to verify controller is reachable
     */
    public function testEndpoint()
    {
        \Log::debug('Test endpoint hit');
        return response()->json(['success' => true, 'message' => 'Controller is working', 'timestamp' => now()]);
    }

    /**
     * Test payment creation without authorization
     */
    public function testPaymentCreation()
    {
        \Log::debug('Test payment endpoint hit');
        
        try {
            \Log::debug('Testing database connection');
            $count = Sale::count();
            \Log::debug('Sale count: ' . $count);
            
            return response()->json([
                'success' => true,
                'message' => 'Database is working',
                'sale_count' => $count,
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Test endpoint error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Receive payment from customer (AJAX)
     */
    /**
     * Receive cash payment (AJAX endpoint)
     * Simple cash-only payment receiver
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
            ],
        ]);
    }
}
