<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Warehouse;
use App\Services\SaleReturnService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Sale Return Controller
 * 
 * Handles all operations related to customer returns:
 * - Listing returns
 * - Creating new returns
 * - Viewing return details
 * - Confirming returns
 * - Cancelling returns
 */
class SaleReturnController extends Controller
{
    protected SaleReturnService $returnService;

    public function __construct(SaleReturnService $returnService)
    {
        $this->returnService = $returnService;
    }

    /**
     * Display a listing of sale returns
     */
    public function index(Request $request): View
    {
        $this->authorize('sales.view');

        $user = auth()->user();
        $query = SaleReturn::with(['sale', 'customer', 'family', 'warehouse', 'creator']);

        // Apply warehouse-level filtering
        if (!$user->isSuperAdmin()) {
            $warehouseIds = $user->warehouses()->pluck('warehouses.id');
            $query->whereIn('warehouse_id', $warehouseIds);
        }

        // Search by return number, customer name, or sale invoice
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale', function ($q) use ($search) {
                        $q->where('invoice_number', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by family
        if ($request->filled('family_id')) {
            $query->where('family_id', $request->family_id);
        }

        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            if ($user->canAccessWarehouse($request->warehouse_id)) {
                $query->where('warehouse_id', $request->warehouse_id);
            }
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('return_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('return_date', '<=', $request->date_to);
        }

        $returns = $query->orderBy('return_date', 'desc')->latest('created_at')->paginate(10)->withQueryString();

        // Get warehouses the user can see
        $warehouses = $user->isSuperAdmin()
            ? Warehouse::active()->orderBy('name')->get()
            : $user->warehouses()->where('status', 'active')->orderBy('name')->get();

        return view('admin.sale-returns.index', compact('returns', 'warehouses'));
    }

    /**
     * Show the form for creating a new sale return
     */
    public function create(Request $request): View
    {
        $this->authorize('sales.create');

        $user = auth()->user();

        // If sale_id is provided, show the return creation form
        if ($request->filled('sale_id')) {
            $sale = Sale::with(['customer', 'family', 'warehouse', 'items'])
                ->findOrFail($request->sale_id);

            if ($sale->status !== Sale::STATUS_CONFIRMED) {
                return redirect()->route('admin.sale-returns.create')
                    ->with('error', 'Can only create returns for confirmed sales.');
            }

            // Check user has access to this warehouse
            if (!$user->isSuperAdmin() && !$user->canAccessWarehouse($sale->warehouse_id)) {
                abort(403, 'You do not have permission to create returns for this sale.');
            }

            return view('admin.sale-returns.form', compact('sale'));
        }

        // Show list of sales to select from
        $query = Sale::where('status', Sale::STATUS_CONFIRMED)
            ->with(['customer', 'family', 'warehouse'])
            ->orderBy('sale_date', 'desc');

        // Apply warehouse filtering based on user permissions
        if (!$user->isSuperAdmin()) {
            $warehouseIds = $user->warehouses()->pluck('warehouses.id');
            $query->whereIn('warehouse_id', $warehouseIds);
        }

        $sales = $query->paginate(20);

        return view('admin.sale-returns.create', compact('sales'));
    }

    /**
     * Search for sales to create return (AJAX)
     */
    public function searchSales(Request $request): JsonResponse
    {
        $this->authorize('sales.view');

        $search = $request->input('search', '');
        $user = auth()->user();

        $query = Sale::with(['customer', 'family', 'warehouse'])
            ->where('status', Sale::STATUS_CONFIRMED);

        // Apply warehouse filtering
        if (!$user->isSuperAdmin()) {
            $warehouseIds = $user->warehouses()->pluck('warehouses.id');
            $query->whereIn('warehouse_id', $warehouseIds);
        }

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $sales = $query->latest('sale_date')->limit(20)->get();

        return response()->json($sales->map(function ($sale) {
            return [
                'id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'customer_name' => $sale->customer ? $sale->customer->name : 'Walk-in Customer',
                'customer_phone' => $sale->customer ? $sale->customer->phone : $sale->walkin_customer_contact,
                'family_name' => $sale->family ? $sale->family->name : null,
                'warehouse_name' => $sale->warehouse ? $sale->warehouse->name : 'Unknown',
                'sale_date' => $sale->sale_date ? $sale->sale_date->format('d M Y') : date('d M Y'),
                'total_amount' => $sale->total_amount ?? 0,
                'paid_amount' => $sale->paid_amount ?? 0,
                'outstanding' => ($sale->total_amount ?? 0) - ($sale->paid_amount ?? 0),
                'payment_status' => $sale->payment_status ?? 'unpaid',
            ];
        }));
    }

    /**
     * Get sale return summary (AJAX)
     * Shows returnable items and payment info for a sale
     */
    public function getSaleReturnSummary($saleId): JsonResponse
    {
        $this->authorize('sales.view');

        try {
            $sale = Sale::with(['customer', 'family', 'warehouse', 'items.product'])
                ->findOrFail($saleId);

            // Verify user has access to this warehouse
            if (!auth()->user()->canAccessWarehouse($sale->warehouse_id)) {
                return response()->json(['error' => 'You do not have access to this warehouse.'], 403);
            }

            // Get return summary
            $returnSummary = $this->returnService->getSaleReturnSummary($sale);

            // Calculate payment info
            $totalPaid = $sale->paid_amount + $sale->total_additional_payments;
            $outstanding = max(0, $sale->total_amount - $totalPaid);

            $paymentStatus = 'Paid';
            if ($totalPaid == 0) {
                $paymentStatus = 'Unpaid';
            } elseif ($outstanding > 0) {
                $paymentStatus = 'Partially Paid';
            }

            return response()->json([
                'sale' => [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'customer_id' => $sale->customer_id,
                    'customer_name' => $sale->customer ? $sale->customer->name : 'Walk-in Customer',
                    'family_id' => $sale->family_id,
                    'family_name' => $sale->family ? $sale->family->name : null,
                    'warehouse_id' => $sale->warehouse_id,
                    'warehouse_name' => $sale->warehouse->name,
                    'sale_date' => $sale->sale_date->format('d M Y'),
                    'total_amount' => $sale->total_amount,
                    'paid_amount' => $totalPaid,
                    'outstanding' => $outstanding,
                    'payment_status' => $paymentStatus,
                ],
                'items' => $returnSummary,
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created sale return
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('sales.create');

        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'return_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        try {
            $sale = Sale::findOrFail($validated['sale_id']);

            // Verify user has access to this warehouse
            if (!auth()->user()->canAccessWarehouse($sale->warehouse_id)) {
                return back()->with('error', 'You do not have access to this warehouse.')->withInput();
            }

            // Create the return (draft status)
            $return = $this->returnService->createReturn(
                $sale,
                $validated['items'],
                [
                    'return_date' => $validated['return_date'],
                    'reason' => $validated['reason'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]
            );

            // Automatically confirm the return (like sales do)
            $this->returnService->confirmReturn($return);

            return redirect()->route('admin.sale-returns.show', $return)
                ->with('success', 'Sale return created and confirmed successfully. Stock has been adjusted and customer balance updated.');

        } catch (Exception $e) {
            return back()->withInput()
                ->with('error', 'Error creating return: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified sale return
     */
    public function show($id): View
    {
        $this->authorize('sales.view');

        $return = SaleReturn::with([
            'sale.customer',
            'sale.family',
            'customer',
            'family',
            'warehouse',
            'items.product',
            'items.saleItem',
            'creator',
            'confirmer',
        ])->findOrFail($id);

        // Verify user has access to this warehouse
        if (!auth()->user()->canAccessWarehouse($return->warehouse_id)) {
            abort(403, 'You do not have access to this warehouse.');
        }

        // Calculate payment info for original sale
        $sale = $return->sale;
        $totalPaid = $sale->paid_amount + $sale->total_additional_payments;
        $outstanding = max(0, $sale->total_amount - $totalPaid);

        $paymentStatus = 'Paid';
        if ($totalPaid == 0) {
            $paymentStatus = 'Unpaid';
        } elseif ($outstanding > 0) {
            $paymentStatus = 'Partially Paid';
        }

        $paymentInfo = [
            'total_amount' => $sale->total_amount,
            'paid_amount' => $totalPaid,
            'outstanding' => $outstanding,
            'payment_status' => $paymentStatus,
        ];

        return view('admin.sale-returns.show', compact('return', 'paymentInfo'));
    }

    /**
     * Confirm a sale return
     */
    public function confirm($id): RedirectResponse
    {
        $this->authorize('sales.approve');

        try {
            $return = SaleReturn::findOrFail($id);

            // Verify user has access to this warehouse
            if (!auth()->user()->canAccessWarehouse($return->warehouse_id)) {
                return back()->with('error', 'You do not have access to this warehouse.');
            }

            $this->returnService->confirmReturn($return);

            return redirect()->route('admin.sale-returns.show', $return)
                ->with('success', 'Sale return confirmed successfully. Stock has been adjusted and customer balance updated.');

        } catch (Exception $e) {
            return back()->with('error', 'Error confirming return: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a sale return
     */
    public function cancel(Request $request, $id): RedirectResponse
    {
        $this->authorize('sales.cancel');

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $return = SaleReturn::findOrFail($id);

            // Verify user has access to this warehouse
            if (!auth()->user()->canAccessWarehouse($return->warehouse_id)) {
                return back()->with('error', 'You do not have access to this warehouse.');
            }

            $this->returnService->cancelReturn($return, $request->reason);

            return redirect()->route('admin.sale-returns.show', $return)
                ->with('success', 'Sale return cancelled successfully.');

        } catch (Exception $e) {
            return back()->with('error', 'Error cancelling return: ' . $e->getMessage());
        }
    }

    /**
     * Get all returns for a specific sale (AJAX)
     */
    public function getSaleReturns($saleId): JsonResponse
    {
        $this->authorize('sales.view');

        try {
            $returns = $this->returnService->getSaleReturns($saleId);

            return response()->json($returns->map(function ($return) {
                return [
                    'id' => $return->id,
                    'return_number' => $return->return_number,
                    'return_date' => $return->return_date->format('d M Y'),
                    'total_return_amount' => $return->total_return_amount,
                    'status' => $return->status,
                    'status_label' => $return->status_label,
                    'status_badge' => $return->status_badge,
                    'reason' => $return->reason,
                ];
            }));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
