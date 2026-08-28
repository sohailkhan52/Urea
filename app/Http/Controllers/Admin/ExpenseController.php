<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses.
     */
    public function index(Request $request): View
    {
        $this->authorize('expenses.view');

        $user = auth()->user();
        $query = Expense::with(['creator', 'warehouse']);

        // Apply warehouse-level filtering
        if (!$user->isSuperAdmin()) {
            $warehouseIds = $user->warehouses()->pluck('warehouses.id');
            $query->whereIn('warehouse_id', $warehouseIds);
        }

        // Search by expense item
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by warehouse (only if user has access)
        if ($request->filled('warehouse_id')) {
            if ($user->canAccessWarehouse($request->warehouse_id)) {
                $query->byWarehouse($request->warehouse_id);
            } else {
                abort(403, 'You do not have access to this warehouse.');
            }
        }

        // Get expenses ordered by latest first, paginated at 20 per page
        $expenses = $query->latest()->paginate(20)->withQueryString();

        // Get warehouses the user can see
        $warehouses = $user->isSuperAdmin()
            ? Warehouse::active()->orderBy('name')->get()
            : $user->warehouses()->where('status', 'active')->orderBy('name')->get();

        // Calculate total expenses for current filtered view
        $totalExpenses = $query->sum('cost');

        return view('admin.expenses.index', compact('expenses', 'warehouses', 'totalExpenses'));
    }

    /**
     * Show the form for creating a new expense.
     */
    public function create(): View
    {
        $this->authorize('expenses.create');

        $user = auth()->user();

        // Get warehouses the user can access
        $warehouses = $user->isSuperAdmin()
            ? Warehouse::active()->orderBy('name')->get()
            : $user->warehouses()->where('status', 'active')->orderBy('name')->get();

        // If user is not super admin and has only one warehouse, pre-select it
        $defaultWarehouse = null;
        if (!$user->isSuperAdmin() && $warehouses->count() === 1) {
            $defaultWarehouse = $warehouses->first();
        }

        return view('admin.expenses.create', compact('warehouses', 'defaultWarehouse'));
    }

    /**
     * Store a newly created expense in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('expenses.create');

        $user = auth()->user();

        // Validate input
        $validated = $request->validate([
            'expense_item' => 'required|string|max:255',
            'cost' => 'required|numeric|gt:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ], [
            'expense_item.required' => 'Expense item is required.',
            'expense_item.max' => 'Expense item must not exceed 255 characters.',
            'cost.required' => 'Cost is required.',
            'cost.numeric' => 'Cost must be a valid number.',
            'cost.gt' => 'Cost must be greater than 0.',
            'warehouse_id.exists' => 'Selected warehouse does not exist.',
        ]);

        // Ensure non-super-admin users can only create for their warehouses
        if (!$user->isSuperAdmin() && $validated['warehouse_id']) {
            if (!$user->canAccessWarehouse($validated['warehouse_id'])) {
                abort(403, 'You do not have access to this warehouse.');
            }
        }

        // If user is not super admin and warehouse_id is not provided, use their default warehouse
        if (!$user->isSuperAdmin() && !$validated['warehouse_id']) {
            $warehouse = $user->getAssignedWarehouse();
            if (!$warehouse) {
                return back()->with('error', 'You must be assigned to a warehouse to create expenses.');
            }
            $validated['warehouse_id'] = $warehouse->id;
        }

        // Create the expense
        $expense = Expense::create([
            'expense_item' => $validated['expense_item'],
            'cost' => $validated['cost'],
            'warehouse_id' => $validated['warehouse_id'],
            'created_by' => $user->id,
        ]);

        return redirect()->route('admin.expenses.index')
            ->with('success', "Expense '{$expense->expense_item}' created successfully!");
    }

    /**
     * Show the form for editing the specified expense.
     */
    public function edit(Expense $expense): View
    {
        $this->authorize('expenses.edit', $expense);

        $user = auth()->user();

        // Check warehouse access
        if (!$user->isSuperAdmin() && $expense->warehouse_id && !$user->canAccessWarehouse($expense->warehouse_id)) {
            abort(403, 'You do not have access to this expense.');
        }

        // Get warehouses the user can access
        $warehouses = $user->isSuperAdmin()
            ? Warehouse::active()->orderBy('name')->get()
            : $user->warehouses()->where('status', 'active')->orderBy('name')->get();

        return view('admin.expenses.edit', compact('expense', 'warehouses'));
    }

    /**
     * Update the specified expense in storage.
     */
    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorize('expenses.edit', $expense);

        $user = auth()->user();

        // Check warehouse access
        if (!$user->isSuperAdmin() && $expense->warehouse_id && !$user->canAccessWarehouse($expense->warehouse_id)) {
            abort(403, 'You do not have access to this expense.');
        }

        // Validate input
        $validated = $request->validate([
            'expense_item' => 'required|string|max:255',
            'cost' => 'required|numeric|gt:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ], [
            'expense_item.required' => 'Expense item is required.',
            'expense_item.max' => 'Expense item must not exceed 255 characters.',
            'cost.required' => 'Cost is required.',
            'cost.numeric' => 'Cost must be a valid number.',
            'cost.gt' => 'Cost must be greater than 0.',
            'warehouse_id.exists' => 'Selected warehouse does not exist.',
        ]);

        // Ensure non-super-admin users cannot change warehouse
        if (!$user->isSuperAdmin() && isset($validated['warehouse_id'])) {
            $validated['warehouse_id'] = $expense->warehouse_id; // Keep original warehouse
        }

        // Update the expense (created_at will not be modified)
        $expense->update([
            'expense_item' => $validated['expense_item'],
            'cost' => $validated['cost'],
            'warehouse_id' => $validated['warehouse_id'],
        ]);

        return redirect()->route('admin.expenses.index')
            ->with('success', "Expense '{$expense->expense_item}' updated successfully!");
    }

    /**
     * Delete the specified expense from storage.
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorize('expenses.delete', $expense);

        $user = auth()->user();

        // Check warehouse access
        if (!$user->isSuperAdmin() && $expense->warehouse_id && !$user->canAccessWarehouse($expense->warehouse_id)) {
            abort(403, 'You do not have access to this expense.');
        }

        $expenseItem = $expense->expense_item;
        $expense->delete();

        return redirect()->route('admin.expenses.index')
            ->with('success', "Expense '{$expenseItem}' deleted successfully!");
    }
}
