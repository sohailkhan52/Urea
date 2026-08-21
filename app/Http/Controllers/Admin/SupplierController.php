<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSupplierRequest;
use App\Http\Requests\Admin\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers.
     */
    public function index(Request $request): View
    {
        $this->authorize('suppliers.view');

        // Only super admin can view suppliers
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can manage suppliers.');
        }

        $query = Supplier::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('ntn', 'like', "%{$search}%");
            });
        }

        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $suppliers = $query->latest()->paginate(15)->withQueryString();
        
        // Get cities for filter
        $cities = Supplier::getCities();

        return view('admin.suppliers.index', compact('suppliers', 'cities'));
    }

    /**
     * Show the form for creating a new supplier.
     */
    public function create(): View
    {
        $this->authorize('suppliers.create');

        return view('admin.suppliers.create');
    }

    /**
     * Store a newly created supplier in storage.
     */
    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $this->authorize('suppliers.create');

        $data = $request->validated();

        $supplier = Supplier::create($data);

        // Log activity
        Log::info('Supplier created', [
            'created_by' => Auth::id(),
            'supplier_id' => $supplier->id,
            'supplier_name' => $supplier->name,
        ]);

        // Dispatch SupplierCreated event to trigger welcome email
        \App\Events\SupplierCreated::dispatch($supplier);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    /**
     * Display the specified supplier.
     */
    public function show(Supplier $supplier): View
    {
        $this->authorize('suppliers.view');

        // Only super admin can view supplier details
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can view supplier details.');
        }

        // TODO: Load purchases when Purchase module is implemented
        // $supplier->load(['purchases' => function ($query) {
        //     $query->latest()->limit(10);
        // }]);

        return view('admin.suppliers.show', compact('supplier'));
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit(Supplier $supplier): View
    {
        $this->authorize('suppliers.update');

        return view('admin.suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $this->authorize('suppliers.update');

        $data = $request->validated();

        $supplier->update($data);

        // Log activity
        Log::info('Supplier updated', [
            'updated_by' => Auth::id(),
            'supplier_id' => $supplier->id,
            'supplier_name' => $supplier->name,
        ]);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified supplier from storage (soft delete).
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorize('suppliers.delete');

        // Check if supplier can be deleted
        if (!$supplier->canBeDeleted()) {
            return back()->with('error', 'Cannot delete this supplier. It has associated purchase orders.');
        }

        $supplierName = $supplier->name;

        // Soft delete
        $supplier->delete();

        // Log activity
        Log::warning('Supplier deleted', [
            'deleted_by' => Auth::id(),
            'supplier_name' => $supplierName,
        ]);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    /**
     * Activate a supplier.
     */
    public function activate(Supplier $supplier): RedirectResponse
    {
        $this->authorize('suppliers.update');

        if ($supplier->status === Supplier::STATUS_ACTIVE) {
            return back()->with('info', 'Supplier is already active.');
        }

        $supplier->update(['status' => Supplier::STATUS_ACTIVE]);

        // Log activity
        Log::info('Supplier activated', [
            'activated_by' => Auth::id(),
            'supplier_id' => $supplier->id,
            'supplier_name' => $supplier->name,
        ]);

        return back()->with('success', 'Supplier activated successfully.');
    }

    /**
     * Deactivate a supplier.
     */
    public function deactivate(Supplier $supplier): RedirectResponse
    {
        $this->authorize('suppliers.update');

        if ($supplier->status === Supplier::STATUS_INACTIVE) {
            return back()->with('info', 'Supplier is already inactive.');
        }

        $supplier->update(['status' => Supplier::STATUS_INACTIVE]);

        // Log activity
        Log::warning('Supplier deactivated', [
            'deactivated_by' => Auth::id(),
            'supplier_id' => $supplier->id,
            'supplier_name' => $supplier->name,
        ]);

        return back()->with('success', 'Supplier deactivated successfully.');
    }
}
