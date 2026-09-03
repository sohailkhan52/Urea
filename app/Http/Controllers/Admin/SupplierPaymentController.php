<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchasePayment;
use App\Models\Supplier;
use App\Services\SupplierPayableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierPaymentController extends Controller
{
    protected SupplierPayableService $payableService;

    public function __construct(SupplierPayableService $payableService)
    {
        $this->payableService = $payableService;
    }

    /**
     * Show make payment form
     */
    public function create(Supplier $supplier)
    {
        $this->authorize('purchases.update');

        $summary = $this->payableService->getSupplierSummary($supplier);

        return view('admin.supplier-payments.create', compact('supplier', 'summary'));
    }

    /**
     * Store a new supplier payment
     */
    public function store(Request $request, Supplier $supplier)
    {
        $this->authorize('purchases.update');

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,easypaisa,jazz_cash,cheque,other',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $payment = $this->payableService->recordPayment(
                supplier: $supplier,
                amount: (float)$validated['amount'],
                paymentMethod: $validated['payment_method'],
                paymentDate: $validated['payment_date'],
                referenceNumber: $validated['reference_number'],
                notes: $validated['notes']
            );

            return redirect()->route('admin.reports.supplier.ledger', $supplier)
                ->with('success', "Payment of Rs. " . number_format($validated['amount'], 2) . " recorded successfully for {$supplier->name}");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error recording payment: ' . $e->getMessage());
        }
    }

    /**
     * Store payment via AJAX (for inline form)
     */
    public function storeAjax(Request $request, Supplier $supplier)
    {
        $this->authorize('purchases.update');

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,easypaisa,jazz_cash,cheque,other',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $payment = $this->payableService->recordPayment(
                supplier: $supplier,
                amount: (float)$validated['amount'],
                paymentMethod: $validated['payment_method'],
                paymentDate: $validated['payment_date'],
                referenceNumber: $validated['reference_number'],
                notes: $validated['notes']
            );

            // Get updated summary
            $summary = $this->payableService->getSupplierSummary($supplier);

            return response()->json([
                'success' => true,
                'message' => "Payment of Rs. " . number_format($validated['amount'], 2) . " recorded successfully",
                'payment' => [
                    'id' => $payment->id,
                    'payment_number' => $payment->payment_number,
                    'amount' => (float)$payment->amount,
                    'payment_date' => $payment->payment_date->format('Y-m-d'),
                    'payment_method' => $payment->payment_method,
                ],
                'summary' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error recording payment: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get payment methods for dropdown
     */
    public function getPaymentMethods()
    {
        $this->authorize('purchases.view');

        return response()->json(PurchasePayment::$methods);
    }

    /**
     * Validate payment amount
     */
    public function validateAmount(Request $request, Supplier $supplier)
    {
        $this->authorize('purchases.view');

        $amount = (float)$request->input('amount', 0);
        $outstanding = $this->payableService->getSupplierOutstandingPayable($supplier);

        if ($amount <= 0) {
            return response()->json([
                'valid' => false,
                'message' => 'Payment amount must be greater than 0',
            ]);
        }

        if ($amount > $outstanding) {
            return response()->json([
                'valid' => false,
                'message' => "Payment cannot exceed outstanding payable (Rs. " . number_format($outstanding, 2) . ")",
            ]);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Payment amount is valid',
            'new_balance' => $outstanding - $amount,
            'new_balance_formatted' => 'Rs. ' . number_format($outstanding - $amount, 2),
        ]);
    }
}
