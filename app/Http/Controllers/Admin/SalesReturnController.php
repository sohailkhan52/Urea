<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\Warehouse;
use App\Services\SalesReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesReturnController extends Controller
{
    protected SalesReturnService $returnService;

    public function __construct(SalesReturnService $returnService)
    {
        $this->returnService = $returnService;
    }

    /**
     * Display a listing of sales returns.
     */
    public function index(Request $request): View
    {
        $this->authorize('sales.view');

        $user = auth()->user();
        $query = SalesReturn::with(['sale.customer', 'sale.warehouse', 'creator']);

        // Apply warehouse-level filtering
        if (!$user->isSuperAdmin()) {
            $userWarehouses = $user->warehouses->pluck('id');
            $query->whereHas('sale', function ($q) use ($userWarehouses) {
                $q->whereIn('warehouse_id', $userWarehouses);
            });
        }

        // Search by return number, invoice number, or customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                    ->orWhereHas('sale', function ($q) use ($search) {
                        $q->where('invoice_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by customer
        if ($request->filled('customer')) {
            $query->where('customer_id', $request->customer);
        }

        // Filter by warehouse
        if ($request->filled('warehouse')) {
            $query->where('warehouse_id', $request->warehouse);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('return_date', [$request->start_date, $request->end_date]);
        }

        $returns = $query->latest('return_date')
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        // Get filter options
        $customers = Customer::active()->orderBy('name')->get();
        $warehouses = $user->isSuperAdmin()
            ? Warehouse::active()->orderBy('name')->get()
            : $user->warehouses()->where('status', 'active')->orderBy('name')->get();

        return view('admin.sales-returns.index', compact(
            'returns',
            'customers',
            'warehouses'
        ));
    }

    /**
     * Show the form for creating a new sales return.
     */
    public function create(Request $request): View
    {
        $this->authorize('sales.create');

        $user = auth()->user();
        
        // If sale_id is provided, load the sale
        $sale = null;
        $saleItems = [];
        $returnableItems = [];
        
        if ($request->filled('sale_id')) {
            $sale = Sale::with(['items.product', 'customer', 'warehouse'])
                ->confirmed()
                ->findOrFail($request->sale_id);

            // Verify user has access to this sale's warehouse
            if (!$user->canAccessWarehouse($sale->warehouse_id)) {
                abort(403, 'You do not have permission to create returns for this sale.');
            }

            // Get returnable quantities for each item
            foreach ($sale->items as $item) {
                $remainingQty = $this->returnService->getRemainingReturnableQuantity($item);
                if ($remainingQty > 0) {
                    $returnableItems[] = [
                        'item' => $item,
                        'remaining_quantity' => $remainingQty,
                        'already_returned' => $item->quantity - $remainingQty,
                    ];
                }
            }
        }

        // Get confirmed sales for selection
        $salesQuery = Sale::with(['customer', 'warehouse'])
            ->confirmed()
            ->forUserWarehouses($user);

        $sales = $salesQuery->latest('sale_date')->limit(100)->get();

        return view('admin.sales-returns.create', compact(
            'sale',
            'returnableItems',
            'sales'
        ));
    }

    /**
     * Store a newly created sales return.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('sales.create');

        $user = auth()->user();

        // Filter out items with 0 quantity before validation
        $requestData = $request->all();
        if ($request->filled('items') && is_array($request->input('items'))) {
            $requestData['items'] = array_filter(
                $request->input('items'),
                fn($item) => (float)($item['quantity'] ?? 0) > 0
            );
            // Re-index the array after filtering
            $requestData['items'] = array_values($requestData['items']);
        }

        // Validate basic fields
        $validated = \Illuminate\Support\Facades\Validator::make($requestData, [
            'sale_id' => 'required|exists:sales,id',
            'return_date' => 'required|date',
            'return_type' => 'required|in:WHOLE_ORDER,PARTIAL_ITEMS',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required_if:return_type,PARTIAL_ITEMS|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.reason' => 'nullable|string|max:500',
        ])->validate();

        $sale = Sale::findOrFail($validated['sale_id']);

        // Verify user has access to this sale's warehouse
        if (!$user->canAccessWarehouse($sale->warehouse_id)) {
            abort(403, 'You do not have permission to create returns for this sale.');
        }

        try {
            // Create return
            $return = $this->returnService->createReturn($sale, $validated);

            // Add items based on return type
            if ($validated['return_type'] === 'WHOLE_ORDER') {
                // Add all remaining items
                $this->returnService->addAllRemainingItems($return, $sale);
            } else {
                // Add selected items
                foreach ($validated['items'] as $itemData) {
                    $saleItem = $sale->items()->findOrFail($itemData['sale_item_id']);
                    
                    $this->returnService->addItem(
                        $return,
                        $saleItem,
                        $itemData['quantity'],
                        $itemData['reason'] ?? null
                    );
                }
            }

            return redirect()->route('admin.sales.returns.show', $return)
                ->with('success', 'Sales return created successfully. Please review and confirm.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error creating sales return: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified sales return.
     */
    public function show(SalesReturn $return): View
    {
        $this->authorize('sales.view');

        // Verify user has access to this return's warehouse
        if (!auth()->user()->canAccessWarehouse($return->warehouse_id)) {
            abort(403, 'You do not have permission to view this return.');
        }

        $return->load([
            'sale.items',
            'customer',
            'warehouse',
            'items.product',
            'items.saleItem',
            'creator',
            'confirmer',
            'canceller',
            'stockMovements.product',
            'ledgerEntries'
        ]);

        $summary = $this->returnService->getReturnSummary($return);

        return view('admin.sales-returns.show', compact('return', 'summary'));
    }

    /**
     * Print sales return document
     */
    public function print(SalesReturn $return): View
    {
        $this->authorize('sales.view');

        // Verify user has access to this return's warehouse
        if (!auth()->user()->canAccessWarehouse($return->warehouse_id)) {
            abort(403, 'You do not have permission to print this return.');
        }

        $return->load([
            'sale',
            'customer',
            'warehouse',
            'items.product',
            'creator',
            'confirmer'
        ]);

        $company = \App\Models\Company::first();

        return view('admin.sales-returns.print', compact('return', 'company'));
    }

    /**
     * Get sale details with returnable quantities (AJAX endpoint)
     */
    public function getSaleDetails(Request $request, Sale $sale)
    {
        $this->authorize('sales.view');
        
        $user = auth()->user();
        
        // Verify user has access to this sale's warehouse
        if (!$user->canAccessWarehouse($sale->warehouse_id)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Load sale with items and relationships
        $sale->load(['items.product', 'customer', 'warehouse']);

        // Get returnable quantities for each item
        $items = [];
        foreach ($sale->items as $item) {
            $returnedQty = $this->returnService->getReturnedQuantity($item->id);
            $remainingQty = $this->returnService->getRemainingReturnableQuantity($item);
            
            $items[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'already_returned' => $returnedQty,
                'remaining_quantity' => $remainingQty,
                'unit_price' => $item->unit_price,
                'discount' => $item->discount ?? 0,
                'max_returnable' => $remainingQty,
            ];
        }

        return response()->json([
            'sale' => [
                'id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'customer' => $sale->customer->name,
                'warehouse' => $sale->warehouse->name,
                'sale_date' => $sale->sale_date->format('d M Y'),
                'total_amount' => $sale->total_amount,
                'paid_amount' => $sale->paid_amount,
                'udhar_amount' => $sale->udhar_amount ?? 0,
            ],
            'items' => $items,
            'is_fully_returned' => empty(array_filter($items, fn($item) => $item['remaining_quantity'] > 0)),
        ]);
    }

    /**
     * Confirm the sales return.
     */
    public function confirm(SalesReturn $return, Request $request): RedirectResponse
    {
        $this->authorize('sales.approve');

        // Verify user has access to this return's warehouse
        if (!auth()->user()->canAccessWarehouse($return->warehouse_id)) {
            abort(403, 'You do not have permission to confirm this return.');
        }

        if (!$return->canBeConfirmed()) {
            return back()->with('error', 'This return cannot be confirmed.');
        }

        $validated = $request->validate([
            'refund_amount' => 'nullable|numeric|min:0',
            'credit_amount' => 'nullable|numeric|min:0',
            'refund_method' => 'nullable|in:cash,bank_transfer,easypaisa,jazz_cash,cheque,other',
            'refund_reference' => 'nullable|string|max:100',
        ]);

        try {
            $this->returnService->confirmReturn($return, $validated);

            return redirect()->route('admin.sales.returns.show', $return)
                ->with('success', 'Sales return confirmed successfully. Stock has been added back to warehouse.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error confirming return: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the sales return.
     */
    public function cancel(SalesReturn $return, Request $request): RedirectResponse
    {
        $this->authorize('sales.cancel');

        // Verify user has access to this return's warehouse
        if (!auth()->user()->canAccessWarehouse($return->warehouse_id)) {
            abort(403, 'You do not have permission to cancel this return.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->returnService->cancelReturn($return, $validated['reason']);

            return redirect()->route('admin.sales.returns.index')
                ->with('success', 'Sales return cancelled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error cancelling return: ' . $e->getMessage());
        }
    }
}
