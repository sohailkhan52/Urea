<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Family;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Warehouse;
use App\Services\SalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SaleReportController extends Controller
{
    protected SalesService $salesService;

    public function __construct(SalesService $salesService)
    {
        $this->salesService = $salesService;
    }

    /**
     * Display sale report with filters and pagination
     */
    public function index(Request $request): View
    {
        $this->authorize('sales.view');

        $user = auth()->user();

        // Start with base query - eager load all necessary relationships to prevent N+1
        $query = Sale::with([
            'customer:id,name,phone',
            'family:id,name',
            'warehouse:id,name',
            'creator:id,name',
            'items' // Load items for profit calculation
        ])->withCount('returns'); // Count returns to check if sale has return history

        // Apply warehouse-level filtering automatically based on user permissions
        $query = $query->forUserWarehouses($user);

        // ========== FILTERS ==========

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by family
        if ($request->filled('family_id')) {
            $query->where('family_id', $request->family_id);
        }

        // Filter by warehouse (only if user has access)
        if ($request->filled('warehouse_id')) {
            if ($user->canAccessWarehouse($request->warehouse_id)) {
                $query->where('warehouse_id', $request->warehouse_id);
            } else {
                abort(403, 'You do not have access to this warehouse.');
            }
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            if ($request->payment_status === 'unpaid') {
                $query->where('paid_amount', 0);
            } elseif ($request->payment_status === 'partial') {
                $query->whereRaw('paid_amount > 0 AND paid_amount < total_amount');
            } elseif ($request->payment_status === 'paid') {
                $query->whereRaw('paid_amount >= total_amount');
            }
        }

        // Search by invoice number or customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('walkin_customer_name', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by created_by (creator)
        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        // ========== SORTING ==========
        // Default: newest first (by sale_date, then by created_at for same-day records)
        $query->orderBy('sale_date', 'desc')->latest('created_at');

        // ========== PAGINATION ==========
        // Use withQueryString() to preserve filters across pages
        $sales = $query->paginate(20)->withQueryString();

        // ========== CALCULATE SUMMARY TOTALS (based on filtered results) ==========
        // Build totals query with same filters as main query
        $totalsQuery = Sale::query()
            ->forUserWarehouses($user)
            ->with('items') // Load items for profit calculation
            ->selectRaw('
                COUNT(*) as total_sales,
                SUM(total_amount) as total_amount_sum,
                SUM(paid_amount) as total_paid_sum,
                SUM(due_amount) as total_due_sum,
                SUM(udhar_amount) as total_udhar_sum
            ');

        // Apply same filters to totals query
        if ($request->filled('date_from')) {
            $totalsQuery->whereDate('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $totalsQuery->whereDate('sale_date', '<=', $request->date_to);
        }
        if ($request->filled('customer_id')) {
            $totalsQuery->where('customer_id', $request->customer_id);
        }
        if ($request->filled('family_id')) {
            $totalsQuery->where('family_id', $request->family_id);
        }
        if ($request->filled('warehouse_id')) {
            $totalsQuery->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('payment_status')) {
            if ($request->payment_status === 'unpaid') {
                $totalsQuery->where('paid_amount', 0);
            } elseif ($request->payment_status === 'partial') {
                $totalsQuery->whereRaw('paid_amount > 0 AND paid_amount < total_amount');
            } elseif ($request->payment_status === 'paid') {
                $totalsQuery->whereRaw('paid_amount >= total_amount');
            }
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $totalsQuery->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('walkin_customer_name', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }
        if ($request->filled('created_by')) {
            $totalsQuery->where('created_by', $request->created_by);
        }

        $totals = $totalsQuery->first();

        // ========== CALCULATE PROFIT/LOSS TOTALS ==========
        // Get all sales matching the filters to calculate profit
        $salesForProfit = Sale::query()
            ->forUserWarehouses($user)
            ->with('items');

        // Apply same filters
        if ($request->filled('date_from')) {
            $salesForProfit->whereDate('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $salesForProfit->whereDate('sale_date', '<=', $request->date_to);
        }
        if ($request->filled('customer_id')) {
            $salesForProfit->where('customer_id', $request->customer_id);
        }
        if ($request->filled('family_id')) {
            $salesForProfit->where('family_id', $request->family_id);
        }
        if ($request->filled('warehouse_id')) {
            $salesForProfit->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('payment_status')) {
            if ($request->payment_status === 'unpaid') {
                $salesForProfit->where('paid_amount', 0);
            } elseif ($request->payment_status === 'partial') {
                $salesForProfit->whereRaw('paid_amount > 0 AND paid_amount < total_amount');
            } elseif ($request->payment_status === 'paid') {
                $salesForProfit->whereRaw('paid_amount >= total_amount');
            }
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $salesForProfit->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('walkin_customer_name', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }
        if ($request->filled('created_by')) {
            $salesForProfit->where('created_by', $request->created_by);
        }

        // Calculate profit metrics using model attributes
        $allSales = $salesForProfit->get();
        $total_revenue = 0;
        $total_cogs = 0;
        $total_profit = 0;
        $total_loss = 0;
        $sales_with_cost_data = 0;

        foreach ($allSales as $sale) {
            if ($sale->has_cost_data) {
                $sales_with_cost_data++;
                $total_revenue += $sale->net_revenue;
                $total_cogs += $sale->total_cogs;
                
                $saleProfit = $sale->gross_profit;
                if ($saleProfit >= 0) {
                    $total_profit += $saleProfit;
                } else {
                    $total_loss += abs($saleProfit);
                }
            }
        }

        $net_profit = $total_profit - $total_loss;
        $avg_margin = $total_revenue > 0 ? (($total_revenue - $total_cogs) / $total_revenue) * 100 : 0;

        // Add profit metrics to totals object
        $totals->total_revenue = $total_revenue;
        $totals->total_cogs = $total_cogs;
        $totals->total_profit = $total_profit;
        $totals->total_loss = $total_loss;
        $totals->net_profit = $net_profit;
        $totals->avg_margin = $avg_margin;
        $totals->sales_with_cost_data = $sales_with_cost_data;

        // ========== GET FILTER OPTIONS ==========
        
        // Get warehouses the user can see
        $warehouses = $user->isSuperAdmin()
            ? Warehouse::active()->orderBy('name')->get()
            : $user->warehouses()->where('status', 'active')->orderBy('name')->get();

        // Get customers - filter by warehouse for non-super-admins
        if ($user->isSuperAdmin()) {
            $customers = Customer::active()->orderBy('name')->get();
        } else {
            $userWarehouses = $user->warehouses()
                ->select('warehouses.id')
                ->pluck('warehouses.id');
            $customers = Customer::active()
                ->whereIn('warehouse_id', $userWarehouses)
                ->orderBy('name')
                ->get();
        }

        // Get families
        $families = Family::orderBy('name')->get();

        // Get creators (users who created sales) - for "Created By" filter
        $creators = Sale::query()
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

        return view('admin.reports.sales.index', compact(
            'sales',
            'totals',
            'customers',
            'families',
            'warehouses',
            'creators'
        ));
    }

    /**
     * Display detailed sale report for a single sale
     */
    public function show(Sale $sale): View
    {
        $this->authorize('sales.view');

        // Verify user has access to this sale's warehouse
        if (!auth()->user()->canAccessWarehouse($sale->warehouse_id)) {
            abort(403, 'You do not have permission to view this sale.');
        }

        // Eager load all relationships
        $sale->load([
            'customer',
            'family',
            'warehouse',
            'items.product',
            'creator',
            'confirmer',
            'customerPayments.receiver',
            'returns' => function ($query) {
                $query->where('status', 'confirmed');
            }
        ]);

        $summary = $this->salesService->getSaleSummary($sale);

        return view('admin.reports.sales.show', compact('sale', 'summary'));
    }

    /**
     * Bulk delete sales with safety checks
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $this->authorize('sales.delete');

        $request->validate([
            'sale_ids' => 'required|array|min:1',
            'sale_ids.*' => 'required|integer|exists:sales,id',
        ]);

        $user = auth()->user();
        $saleIds = $request->sale_ids;
        
        $errors = [];
        $deletedCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();

        try {
            // Fetch all sales with necessary relationships
            $sales = Sale::with(['returns', 'warehouse', 'customerPayments', 'items'])
                ->whereIn('id', $saleIds)
                ->get();

            foreach ($sales as $sale) {
                // ========== SAFETY CHECKS ==========

                // Check 1: Warehouse access
                if (!$user->canAccessWarehouse($sale->warehouse_id)) {
                    $errors[] = "Sale #{$sale->invoice_number}: No permission to delete (warehouse access denied).";
                    $skippedCount++;
                    continue;
                }

                // Check 2: Sale Return protection - DO NOT delete sales with confirmed returns
                $confirmedReturnsCount = $sale->returns()->where('status', 'confirmed')->count();
                if ($confirmedReturnsCount > 0) {
                    $errors[] = "Sale #{$sale->invoice_number}: Cannot be deleted because it has {$confirmedReturnsCount} confirmed return(s).";
                    $skippedCount++;
                    continue;
                }

                // Check 3: If sale is confirmed, we need to restore stock
                if ($sale->isConfirmed()) {
                    try {
                        // Cancel the sale first (this restores stock using existing logic)
                        $this->salesService->cancelSale($sale, 'Deleted via bulk delete in reports');
                    } catch (\Exception $e) {
                        $errors[] = "Sale #{$sale->invoice_number}: Failed to cancel before deletion - {$e->getMessage()}";
                        $skippedCount++;
                        continue;
                    }
                }

                // ========== SAFE DELETION ==========
                try {
                    // Delete related records in correct order
                    
                    // 1. Delete customer payments (if any)
                    $sale->customerPayments()->delete();
                    
                    // 2. Delete sale items
                    $sale->items()->delete();
                    
                    // 3. Delete pending (draft) returns if any
                    $sale->returns()->where('status', 'draft')->delete();
                    
                    // 4. Finally delete the sale itself (soft delete)
                    $sale->delete();
                    
                    $deletedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Sale #{$sale->invoice_number}: Deletion failed - {$e->getMessage()}";
                    $skippedCount++;
                    continue;
                }
            }

            DB::commit();

            // ========== PREPARE RESPONSE MESSAGE ==========
            $message = '';
            
            if ($deletedCount > 0) {
                $message .= "{$deletedCount} sale(s) deleted successfully.";
            }
            
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} sale(s) could not be deleted.";
            }

            if (!empty($errors)) {
                // Show errors as warning
                return redirect()->route('admin.reports.sales.index')
                    ->with('warning', $message)
                    ->with('errors', $errors);
            }

            return redirect()->route('admin.reports.sales.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('admin.reports.sales.index')
                ->with('error', 'Bulk deletion failed: ' . $e->getMessage());
        }
    }
}
