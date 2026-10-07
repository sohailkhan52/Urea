<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturnItem;
use App\Models\Sale;
use App\Models\SaleReturnItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductReportController extends Controller
{
    /**
     * Display products report with filters and pagination
     */
    public function index(Request $request): View
    {
        $this->authorize('products.view');

        // Base query with pagination
        $query = Product::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name or SKU
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter by unit
        if ($request->filled('unit')) {
            $query->where('unit', $request->unit);
        }

        // Sort
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        
        if (in_array($sortBy, ['name', 'purchase_price', 'sale_price', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }
        $products = $query->with(['productUnits'])->paginate($perPage)->withQueryString();

        // Get available units
        $units = Product::getUnits();

        // Calculate totals
        $totals = [
            'total_products' => Product::count(),
            'active_products' => Product::where('status', 'active')->count(),
            'inactive_products' => Product::where('status', 'inactive')->count(),
            'avg_purchase_price' => Product::avg('purchase_price'),
            'avg_sale_price' => Product::avg('sale_price'),
            'total_margin' => Product::where('purchase_price', '>', 0)
                ->get()
                ->average(function ($product) {
                    return $product->sale_price - $product->purchase_price;
                }),
        ];

        return view('admin.reports.products.index', compact('products', 'units', 'totals', 'perPage'));
    }

    public function history(Request $request): View
    {
        $this->authorize('products.view');

        $filters = $this->historyFilters($request);
        $products = Product::query()
            ->with('baseUnit') // Load base unit relationship
            ->when($filters['search'], fn ($query, $search) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            }))
            ->when($filters['product_id'], fn ($query, $productId) => $query->whereKey($productId))
            ->when($filters['unit'], fn ($query, $unit) => $query->where('unit', $unit))
            ->orderBy('name')
            ->get();

        $rows = $products->map(fn ($product) => $this->productHistorySummary($product, $filters));

        return view('admin.reports.products.history', [
            'products' => $rows,
            'allProducts' => Product::orderBy('name')->get(['id', 'name', 'sku', 'unit']),
            'units' => Product::getUnits(),
            'filters' => $filters,
            'summary' => [
                'total_products' => $rows->count(),
                'total_purchased' => $rows->sum('quantity_purchased'),
                'total_sold' => $rows->sum('quantity_sold'),
            ],
        ]);
    }

    public function historyShow(Request $request, Product $product): View
    {
        $this->authorize('products.view');

        // Load baseUnit relationship for display
        $product->load('baseUnit');

        $filters = $this->historyFilters($request);
        $summary = $this->productHistorySummary($product, $filters);
        $transactions = collect();

        // MULTI-UNIT FIX: Use base_quantity for normalized tracking, but also show transaction details
        $purchaseItems = $product->purchaseItems()->with(['purchase', 'unit'])->whereHas('purchase', function ($query) use ($filters) {
            $query->where('status', Purchase::STATUS_CONFIRMED);
            $this->applyDateFilter($query, 'purchase_date', $filters);
        })->get();
        foreach ($purchaseItems as $item) {
            // Use base_quantity for balance calculation, fall back to quantity for legacy records
            $baseQty = $item->base_quantity ?? $item->quantity;
            $transactions->push([
                'date' => $item->purchase->purchase_date,
                'type' => 'Purchase',
                'reference' => $item->purchase->purchase_number,
                'in' => (float) $baseQty,
                'out' => 0,
                'return' => 0,
                // Store transaction details for display
                'transaction_quantity' => $item->quantity,
                'unit_name' => $item->unit->abbreviation ?? $product->unit,
                'conversion_factor' => $item->conversion_factor,
            ]);
        }

        $purchaseReturns = PurchaseReturnItem::where('product_id', $product->id)->with(['purchaseReturn', 'unit'])
            ->whereHas('purchaseReturn', function ($query) use ($filters) {
                $query->where('status', 'confirmed');
                $this->applyDateFilter($query, 'return_date', $filters);
            })->get();
        foreach ($purchaseReturns as $item) {
            $baseQty = $item->base_quantity ?? $item->quantity;
            $transactions->push([
                'date' => $item->purchaseReturn->return_date,
                'type' => 'Purchase Return',
                'reference' => $item->purchaseReturn->return_number,
                'in' => 0,
                'out' => 0,
                'return' => (float) $baseQty,
                'transaction_quantity' => $item->quantity,
                'unit_name' => $item->unit->abbreviation ?? $product->unit,
                'conversion_factor' => $item->conversion_factor,
            ]);
        }

        $saleItems = $product->saleItems()->with(['sale', 'unit'])->whereHas('sale', function ($query) use ($filters) {
            $query->where('status', Sale::STATUS_CONFIRMED);
            $this->applyDateFilter($query, 'sale_date', $filters);
        })->get();
        foreach ($saleItems as $item) {
            $baseQty = $item->base_quantity ?? $item->quantity;
            $transactions->push([
                'date' => $item->sale->sale_date,
                'type' => 'Sale',
                'reference' => $item->sale->invoice_number,
                'in' => 0,
                'out' => (float) $baseQty,
                'return' => 0,
                'transaction_quantity' => $item->quantity,
                'unit_name' => $item->unit->abbreviation ?? $product->unit,
                'conversion_factor' => $item->conversion_factor,
            ]);
        }

        $saleReturns = SaleReturnItem::where('product_id', $product->id)->with(['saleReturn', 'unit'])
            ->whereHas('saleReturn', function ($query) use ($filters) {
                $query->where('status', 'confirmed');
                $this->applyDateFilter($query, 'return_date', $filters);
            })->get();
        foreach ($saleReturns as $item) {
            $baseQty = $item->base_quantity ?? $item->quantity;
            $transactions->push([
                'date' => $item->saleReturn->return_date,
                'type' => 'Sale Return',
                'reference' => $item->saleReturn->return_number,
                'in' => (float) $baseQty,
                'out' => 0,
                'return' => 0,
                'transaction_quantity' => $item->quantity,
                'unit_name' => $item->unit->abbreviation ?? $product->unit,
                'conversion_factor' => $item->conversion_factor,
            ]);
        }

        // Calculate running balance using BASE QUANTITIES
        $balance = 0;
        $transactions = $transactions->sortBy('date')->values()->map(function ($transaction) use (&$balance) {
            $balance += $transaction['in'] - $transaction['out'] - $transaction['return'];
            $transaction['balance'] = $balance;
            return $transaction;
        });

        return view('admin.reports.products.history-show', compact('product', 'summary', 'transactions', 'filters'));
    }

    private function historyFilters(Request $request): array
    {
        return [
            'search' => trim((string) $request->input('search', '')),
            'product_id' => $request->filled('product_id') ? (int) $request->input('product_id') : null,
            'unit' => $request->input('unit'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];
    }

    private function applyDateFilter($query, string $column, array $filters): void
    {
        if ($filters['date_from']) {
            $query->whereDate($column, '>=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $query->whereDate($column, '<=', $filters['date_to']);
        }
    }

    /**
     * Get product history summary with multi-unit support
     * 
     * Uses base_quantity for accurate aggregation across different units.
     * Falls back to quantity for legacy records without base_quantity.
     * 
     * @param Product $product
     * @param array $filters
     * @return object
     */
    private function productHistorySummary(Product $product, array $filters): object
    {
        // MULTI-UNIT FIX: Use base_quantity with fallback to quantity for legacy records
        // We need to use raw SQL to handle the COALESCE for legacy compatibility
        $purchased = (float) $product->purchaseItems()->whereHas('purchase', function ($query) use ($filters) {
            $query->where('status', Purchase::STATUS_CONFIRMED);
            $this->applyDateFilter($query, 'purchase_date', $filters);
        })->sum(\DB::raw('COALESCE(base_quantity, quantity)'));
        
        $purchasedReturns = (float) PurchaseReturnItem::where('product_id', $product->id)->whereHas('purchaseReturn', function ($query) use ($filters) {
            $query->where('status', 'confirmed');
            $this->applyDateFilter($query, 'return_date', $filters);
        })->sum(\DB::raw('COALESCE(base_quantity, quantity)'));
        
        $sold = (float) $product->saleItems()->whereHas('sale', function ($query) use ($filters) {
            $query->where('status', Sale::STATUS_CONFIRMED);
            $this->applyDateFilter($query, 'sale_date', $filters);
        })->sum(\DB::raw('COALESCE(base_quantity, quantity)'));
        
        $soldReturns = (float) SaleReturnItem::where('product_id', $product->id)->whereHas('saleReturn', function ($query) use ($filters) {
            $query->where('status', 'confirmed');
            $this->applyDateFilter($query, 'return_date', $filters);
        })->sum(\DB::raw('COALESCE(base_quantity, quantity)'));

        return (object) [
            'product' => $product,
            'quantity_purchased' => max(0, $purchased - $purchasedReturns),
            'quantity_sold' => max(0, $sold - $soldReturns),
        ];
    }

    /**
     * Delete a product
     */
    public function destroy(Product $product)
    {
        $this->authorize('products.delete');

        $productName = $product->name;
        $product->delete();

        return redirect()->route('admin.reports.products.index')
            ->with('success', "Product '{$productName}' deleted successfully.");
    }

    /**
     * Export products report (CSV or Excel)
     */
    public function export(Request $request)
    {
        $this->authorize('products.view');

        $products = Product::query();

        if ($request->filled('status')) {
            $products->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $products->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $products->get();

        // For now, return JSON
        return response()->json([
            'message' => 'Export functionality coming soon',
            'count' => $products->count(),
        ]);
    }
}
