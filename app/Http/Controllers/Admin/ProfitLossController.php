<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Family;
use App\Models\Sale;
use App\Models\Warehouse;
use App\Services\SalesService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfitLossController extends Controller
{
    protected SalesService $salesService;

    public function __construct(SalesService $salesService)
    {
        $this->salesService = $salesService;
    }

    /**
     * Display profit & loss report with filters and pagination
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

        // Only show confirmed sales (draft/cancelled sales don't affect profit)
        $query->where('status', 'confirmed');

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

        // Filter by profit status (profit, loss, break-even)
        if ($request->filled('profit_status')) {
            $query->whereHas('items', function($q) use ($request) {
                $q->whereNotNull('cost_price');
            });
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
        // Default: newest first
        $query->latest('sale_date');

        // ========== PAGINATION ==========
        // Use withQueryString() to preserve filters across pages
        $sales = $query->paginate(20)->withQueryString();

        // ========== CALCULATE PROFIT/LOSS SUMMARY (based on filtered results) ==========
        // Get all sales matching the filters to calculate profit
        $salesForProfit = Sale::query()
            ->forUserWarehouses($user)
            ->where('status', 'confirmed')
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
        $total_sales = $allSales->count();
        $total_revenue = 0;
        $total_cogs = 0;
        $total_profit = 0;
        $total_loss = 0;
        $sales_with_cost_data = 0;
        $profitable_sales = 0;
        $loss_sales = 0;
        $breakeven_sales = 0;

        foreach ($allSales as $sale) {
            if ($sale->has_cost_data) {
                $sales_with_cost_data++;
                $total_revenue += $sale->net_revenue;
                $total_cogs += $sale->total_cogs;
                
                $saleProfit = $sale->gross_profit;
                if ($saleProfit > 0) {
                    $total_profit += $saleProfit;
                    $profitable_sales++;
                } elseif ($saleProfit < 0) {
                    $total_loss += abs($saleProfit);
                    $loss_sales++;
                } else {
                    $breakeven_sales++;
                }
            }
        }

        $net_profit = $total_profit - $total_loss;
        $avg_margin = $total_revenue > 0 ? (($total_revenue - $total_cogs) / $total_revenue) * 100 : 0;

        // Create totals object
        $totals = (object) [
            'total_sales' => $total_sales,
            'total_revenue' => $total_revenue,
            'total_cogs' => $total_cogs,
            'total_profit' => $total_profit,
            'total_loss' => $total_loss,
            'net_profit' => $net_profit,
            'avg_margin' => $avg_margin,
            'sales_with_cost_data' => $sales_with_cost_data,
            'profitable_sales' => $profitable_sales,
            'loss_sales' => $loss_sales,
            'breakeven_sales' => $breakeven_sales,
        ];

        // Filter sales after pagination if profit_status is specified
        if ($request->filled('profit_status')) {
            $sales->getCollection()->transform(function($sale) use ($request) {
                if (!$sale->has_cost_data) {
                    return null;
                }
                
                $status = $request->profit_status;
                if ($status === 'profit' && $sale->profit_status !== 'profit') {
                    return null;
                }
                if ($status === 'loss' && $sale->profit_status !== 'loss') {
                    return null;
                }
                if ($status === 'breakeven' && $sale->profit_status !== 'breakeven') {
                    return null;
                }
                
                return $sale;
            })->filter();
        }

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

        return view('admin.reports.profit-loss.index', compact(
            'sales',
            'totals',
            'customers',
            'families',
            'warehouses',
            'creators'
        ));
    }

    /**
     * Display detailed profit/loss analysis for a single sale
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

        return view('admin.reports.profit-loss.show', compact('sale', 'summary'));
    }
}
