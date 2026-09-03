<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CustomerPaymentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    protected CustomerPaymentService $paymentService;

    public function __construct(CustomerPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Show customer account statement
     */
    public function statement(Customer $customer, Request $request): View
    {
        $this->authorize('sales.view');

        // Verify user has access to this customer's warehouse
        if (!auth()->user()->canAccessWarehouse($customer->warehouse_id)) {
            abort(403, 'You do not have permission to view this customer account.');
        }

        $customer->load(['family', 'warehouse']);

        // Get date range filters
        $options = [];
        if ($request->filled('start_date')) {
            $options['start_date'] = $request->input('start_date');
        }
        if ($request->filled('end_date')) {
            $options['end_date'] = $request->input('end_date');
        }

        // Get account statement
        $statement = $this->paymentService->getCustomerAccountStatement($customer, $options);

        // Calculate opening balance if date range specified
        $openingBalance = 0;
        if ($request->filled('start_date')) {
            $salesBeforeStart = $customer->sales()
                ->confirmed()
                ->where('sale_date', '<', $request->input('start_date'))
                ->sum('total_amount');

            $initialPaidBeforeStart = $customer->sales()
                ->confirmed()
                ->where('sale_date', '<', $request->input('start_date'))
                ->sum('paid_amount');

            $paymentsBeforeStart = $customer->payments()
                ->where('payment_date', '<', $request->input('start_date'))
                ->sum('amount');

            $openingBalance = $salesBeforeStart - ($initialPaidBeforeStart + $paymentsBeforeStart);
        }

        return view('admin.customers.statement', compact('customer', 'statement', 'openingBalance'));
    }

    /**
     * Export customer account statement (PDF or Excel)
     */
    public function exportStatement(Customer $customer, Request $request)
    {
        $this->authorize('sales.view');

        // Verify user has access
        if (!auth()->user()->canAccessWarehouse($customer->warehouse_id)) {
            abort(403);
        }

        // Get statement data
        $options = [];
        if ($request->filled('start_date')) {
            $options['start_date'] = $request->input('start_date');
        }
        if ($request->filled('end_date')) {
            $options['end_date'] = $request->input('end_date');
        }

        $statement = $this->paymentService->getCustomerAccountStatement($customer, $options);

        $format = $request->input('format', 'pdf');

        if ($format === 'pdf') {
            // Implement PDF export
            // You can use packages like barryvdh/laravel-dompdf
            return response()->json(['message' => 'PDF export coming soon']);
        } elseif ($format === 'excel') {
            // Implement Excel export
            // You can use packages like maatwebsite/excel
            return response()->json(['message' => 'Excel export coming soon']);
        }

        return back()->with('error', 'Invalid export format');
    }
}
