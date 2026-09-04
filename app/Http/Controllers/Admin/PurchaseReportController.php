<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseReportController extends Controller
{
    protected PurchaseService $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    /**
     * Display purchase report with filters and pagination
     */
    public function index(Request $request): View
    {
        $this->authorize('purchases.view');

        $user = auth()->user();

        // Start with base query - eager load all necessary relationships to prevent N+1
        $query = Purchase::with([
            'supplier:id,name,phone',
            'warehouse:id,name',
            'creator:id,name'
        ]);

        // Apply warehouse-level filtering automatically based on user permissions
        $query = $query->forUserWarehouses($user);

        // ========== FILTERS ==========

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->date_to);
        }

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Search by purchase number or supplier name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by created_by (creator)
        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        // ========== SORTING ==========
        // Default: newest first (by purchase_date, then by created_at for same-day records)
        $query->orderBy('purchase_date', 'desc')->latest('created_at');

        // ========== PAGINATION ==========
        // Use withQueryString() to preserve filters across pages
        $purchases = $query->paginate(20)->withQueryString();

        // ========== CALCULATE SUMMARY TOTALS (based on filtered results) ==========
        // Build totals query with same filters as main query
        $totalsQuery = Purchase::query()
            ->forUserWarehouses($user)
            ->selectRaw('
                COUNT(*) as total_purchases,
                SUM(total_amount) as total_amount_sum,
                SUM(paid_amount) as total_paid_sum,
                SUM(total_amount - paid_amount) as total_payable_sum
            ');

        // Apply same filters to totals query
        if ($request->filled('date_from')) {
            $totalsQuery->whereDate('purchase_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $totalsQuery->whereDate('purchase_date', '<=', $request->date_to);
        }
        if ($request->filled('supplier_id')) {
            $totalsQuery->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('payment_status')) {
            $totalsQuery->where('payment_status', $request->payment_status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $totalsQuery->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }
        if ($request->filled('created_by')) {
            $totalsQuery->where('created_by', $request->created_by);
        }

        $totals = $totalsQuery->first();

        // ========== GET FILTER OPTIONS ==========
        
        // Get warehouses the user can see
        $warehouses = $user->isSuperAdmin()
            ? Warehouse::active()->orderBy('name')->get()
            : $user->warehouses()->where('status', 'active')->orderBy('name')->get();

        // Get suppliers
        $suppliers = Supplier::active()->orderBy('name')->get();

        // Get creators (users who created purchases) - for "Created By" filter
        $creators = Purchase::query()
            ->when(!$user->isSuperAdmin(), function ($q) use ($user) {
                $q->forUserWarehouses($user);
            })
            ->with('creator:id,name')
            ->select('created_by')
            ->distinct()
            ->get()
            ->pluck('creator')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        return view('admin.reports.purchases.index', compact(
            'purchases',
            'totals',
            'suppliers',
            'warehouses',
            'creators'
        ));
    }

    /**
     * Display detailed purchase report for a single purchase
     */
    public function show(Purchase $purchase): View
    {
        $this->authorize('purchases.view');

        // Verify user has access to this purchase's warehouse
        if (!auth()->user()->canAccessWarehouse($purchase->warehouse_id)) {
            abort(403, 'You do not have permission to view this purchase.');
        }

        // Eager load all relationships
        $purchase->load([
            'supplier',
            'warehouse',
            'items.product',
            'creator',
            'confirmer',
            'payments'
        ]);

        $summary = $this->purchaseService->getPurchaseSummary($purchase);

        return view('admin.reports.purchases.show', compact('purchase', 'summary'));
    }

    /**
     * Bulk delete purchases with safety checks
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $this->authorize('purchases.delete');

        $request->validate([
            'purchase_ids' => 'required|array|min:1',
            'purchase_ids.*' => 'required|integer|exists:purchases,id',
        ]);

        $user = auth()->user();
        $purchaseIds = $request->purchase_ids;
        
        $errors = [];
        $deletedCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();

        try {
            // Fetch all purchases with necessary relationships
            $purchases = Purchase::with(['warehouse', 'payments', 'items'])
                ->whereIn('id', $purchaseIds)
                ->get();

            foreach ($purchases as $purchase) {
                // ========== SAFETY CHECKS ==========

                // Check 1: Warehouse access
                if (!$user->canAccessWarehouse($purchase->warehouse_id)) {
                    $errors[] = "Purchase #{$purchase->purchase_number}: No permission to delete (warehouse access denied).";
                    $skippedCount++;
                    continue;
                }

                // Check 2: If purchase is confirmed, we need to restore stock
                if ($purchase->isConfirmed()) {
                    try {
                        // Cancel the purchase first (this restores stock using existing logic)
                        $this->purchaseService->cancelPurchase($purchase, 'Deleted via bulk delete in reports');
                    } catch (\Exception $e) {
                        $errors[] = "Purchase #{$purchase->purchase_number}: Failed to cancel before deletion - {$e->getMessage()}";
                        $skippedCount++;
                        continue;
                    }
                }

                // ========== SAFE DELETION ==========
                try {
                    // Delete related records in correct order
                    
                    // 1. Delete purchase payments (if any)
                    $purchase->payments()->delete();
                    
                    // 2. Delete purchase items
                    $purchase->items()->delete();
                    
                    // 3. Delete pending (draft) returns if any
                    $purchase->returns()->where('status', 'draft')->delete();
                    
                    // 4. Finally delete the purchase itself (soft delete)
                    $purchase->delete();
                    
                    $deletedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Purchase #{$purchase->purchase_number}: Deletion failed - {$e->getMessage()}";
                    $skippedCount++;
                    continue;
                }
            }

            DB::commit();

            // ========== PREPARE RESPONSE MESSAGE ==========
            $message = '';
            
            if ($deletedCount > 0) {
                $message .= "{$deletedCount} purchase(s) deleted successfully.";
            }
            
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} purchase(s) could not be deleted.";
            }

            if (!empty($errors)) {
                // Show errors as warning
                return redirect()->route('admin.reports.purchases.index')
                    ->with('warning', $message)
                    ->with('errors', $errors);
            }

            return redirect()->route('admin.reports.purchases.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('admin.reports.purchases.index')
                ->with('error', 'Bulk deletion failed: ' . $e->getMessage());
        }
    }
}
