<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use Illuminate\View\View;

class SupplierPayableController extends Controller
{
    /**
     * Display supplier payables list
     */
    public function index(): View
    {
        $this->authorize('purchases.view');

        $user = auth()->user();
        
        // Get all confirmed purchases
        $purchasesQuery = Purchase::where('status', 'confirmed');
        
        if (!$user->isSuperAdmin()) {
            $warehouseIds = $user->warehouses()->pluck('warehouses.id')->toArray();
            if (empty($warehouseIds)) {
                return view('admin.supplier-payables.index', [
                    'suppliers' => collect(),
                    'summary' => [
                        'total_outstanding' => 0,
                        'total_purchases' => 0,
                        'total_paid' => 0,
                        'supplier_count' => 0,
                    ]
                ]);
            }
            $purchasesQuery->whereIn('warehouse_id', $warehouseIds);
        }
        
        $allPurchases = $purchasesQuery->get();
        
        // Calculate summary totals
        $totalPurchases = $allPurchases->sum('total_amount');
        $totalPaid = $allPurchases->sum('paid_amount');
        
        $returnsQuery = PurchaseReturn::where('status', 'confirmed');
        if (!$user->isSuperAdmin()) {
            $returnsQuery->whereIn('warehouse_id', $user->warehouses()->pluck('warehouses.id')->toArray());
        }
        $totalReturns = $returnsQuery->sum('total_amount');
        
        $totalOutstanding = max(0, $totalPurchases - $totalPaid - $totalReturns);
        
        // Get unique supplier IDs from purchases
        $supplierIds = $allPurchases->pluck('supplier_id')->unique()->toArray();
        
        // Get suppliers and calculate their payables
        $suppliers = collect();
        
        if (!empty($supplierIds)) {
            $allSuppliers = Supplier::whereIn('id', $supplierIds)
                ->get()
                ->map(function ($supplier) use ($allPurchases, $user) {
                    // Get purchases for this supplier
                    $supplierPurchases = $allPurchases->where('supplier_id', $supplier->id);
                    
                    $supplierTotal = $supplierPurchases->sum('total_amount');
                    $supplierPaid = $supplierPurchases->sum('paid_amount');
                    
                    // Get returns for this supplier
                    $supplierReturns = PurchaseReturn::where('supplier_id', $supplier->id)
                        ->where('status', 'confirmed');
                    
                    if (!$user->isSuperAdmin()) {
                        $warehouseIds = $user->warehouses()->pluck('warehouses.id')->toArray();
                        $supplierReturns->whereIn('warehouse_id', $warehouseIds);
                    }
                    
                    $supplierReturnAmount = $supplierReturns->sum('total_amount');
                    $supplierOutstanding = max(0, $supplierTotal - $supplierPaid - $supplierReturnAmount);
                    
                    return (object)[
                        'id' => $supplier->id,
                        'name' => $supplier->name,
                        'company_name' => $supplier->company_name,
                        'phone' => $supplier->phone,
                        'total_purchases' => $supplierTotal,
                        'total_paid' => $supplierPaid,
                        'total_returns' => $supplierReturnAmount,
                        'outstanding_payable' => $supplierOutstanding,
                    ];
                })
                ->filter(function ($supplier) {
                    return $supplier->outstanding_payable > 0;
                })
                ->sortByDesc('outstanding_payable')
                ->values();
            
            // Paginate the results (10 per page)
            $page = \Illuminate\Pagination\Paginator::resolveCurrentPage();
            $perPage = 10;
            $suppliers = new \Illuminate\Pagination\LengthAwarePaginator(
                $allSuppliers->forPage($page, $perPage)->values(),
                $allSuppliers->count(),
                $perPage,
                $page,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );
        }
        
        $summary = [
            'total_outstanding' => $totalOutstanding,
            'total_purchases' => $totalPurchases,
            'total_paid' => $totalPaid,
            'supplier_count' => $suppliers->count(),
        ];
        
        return view('admin.supplier-payables.index', compact('suppliers', 'summary'));
    }
    
    /**
     * Show supplier detail page
     */
    public function show($supplierId): View
    {
        $this->authorize('purchases.view');
        
        $user = auth()->user();
        $supplier = Supplier::findOrFail($supplierId);
        
        // Get all confirmed purchases for this supplier
        $purchasesQuery = Purchase::where('supplier_id', $supplierId)
            ->where('status', 'confirmed');
        
        if (!$user->isSuperAdmin()) {
            $warehouseIds = $user->warehouses()->pluck('warehouses.id')->toArray();
            $purchasesQuery->whereIn('warehouse_id', $warehouseIds);
        }
        
        $purchases = $purchasesQuery->get();
        
        $totalPurchases = $purchases->sum('total_amount');
        $totalPaid = $purchases->sum('paid_amount');
        
        $returnsQuery = PurchaseReturn::where('supplier_id', $supplierId)
            ->where('status', 'confirmed');
        
        if (!$user->isSuperAdmin()) {
            $warehouseIds = $user->warehouses()->pluck('warehouses.id')->toArray();
            $returnsQuery->whereIn('warehouse_id', $warehouseIds);
        }
        
        $totalReturns = $returnsQuery->sum('total_amount');
        $outstanding = max(0, $totalPurchases - $totalPaid - $totalReturns);
        
        $data = [
            'supplier' => $supplier,
            'total_purchases' => $totalPurchases,
            'total_paid' => $totalPaid,
            'total_returns' => $totalReturns,
            'outstanding' => $outstanding,
            'purchases' => $purchases,
        ];
        
        return view('admin.supplier-payables.show', $data);
    }
    
    /**
     * Show payment history
     */
    public function history($supplierId): View
    {
        $this->authorize('purchases.view');
        
        $user = auth()->user();
        $supplier = Supplier::findOrFail($supplierId);
        
        // Get only cash payments recorded through Supplier Payables (payment_method = 'cash')
        $paymentsQuery = \App\Models\PurchasePayment::where('supplier_id', $supplierId)
            ->where('payment_method', 'cash')  // Only payments from Supplier Payables feature
            ->with('purchase.warehouse');
        
        if (!$user->isSuperAdmin()) {
            $warehouseIds = $user->warehouses()->pluck('warehouses.id')->toArray();
            $paymentsQuery->whereHas('purchase', function ($q) use ($warehouseIds) {
                $q->whereIn('warehouse_id', $warehouseIds);
            });
        }
        
        $payments = $paymentsQuery->orderByDesc('payment_date')->paginate(20);
        
        return view('admin.supplier-payables.history', [
            'supplier' => $supplier,
            'payments' => $payments,
        ]);
    }
    
    /**
     * Record cash payment
     */
    public function payment($supplierId)
    {
        $this->authorize('purchases.create');
        
        $user = auth()->user();
        $supplier = Supplier::findOrFail($supplierId);
        
        $request = request();
        $amount = (float) $request->input('amount');
        $reference = $request->input('reference');
        $notes = $request->input('notes');
        
        // Validate amount
        if ($amount <= 0) {
            return response()->json(['success' => false, 'message' => 'Amount must be greater than 0']);
        }
        
        // Find oldest unpaid purchase
        $purchase = Purchase::where('supplier_id', $supplierId)
            ->where('status', 'confirmed')
            ->whereColumn('paid_amount', '<', 'total_amount')
            ->oldest('purchase_date')
            ->first();
        
        if (!$purchase) {
            return response()->json(['success' => false, 'message' => 'No unpaid purchases found']);
        }
        
        // Check warehouse access
        if (!$user->canAccessWarehouse($purchase->warehouse_id)) {
            return response()->json(['success' => false, 'message' => 'You do not have access to this warehouse']);
        }
        
        // Create payment record
        try {
            $payment = \App\Models\PurchasePayment::create([
                'payment_number' => 'PP-' . now()->format('YmdHis') . '-' . str_pad(round(microtime(true) * 10000) % 10000, 4, '0', STR_PAD_LEFT),
                'supplier_id' => $supplierId,
                'purchase_id' => $purchase->id,
                'amount' => $amount,
                'payment_method' => 'cash',
                'payment_date' => now(),
                'reference_number' => $reference,
                'notes' => $notes,
                'recorded_by' => $user->id,
            ]);
            
            // Update purchase paid_amount
            $newPaidAmount = $purchase->paid_amount + $amount;
            $purchase->update(['paid_amount' => $newPaidAmount]);
            
            // Update payment status
            if ($newPaidAmount >= $purchase->total_amount) {
                $purchase->update(['payment_status' => 'paid']);
            } else {
                $purchase->update(['payment_status' => 'partial']);
            }
            
            return response()->json(['success' => true, 'message' => 'Payment recorded successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
