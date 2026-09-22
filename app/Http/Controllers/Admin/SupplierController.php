<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers.
     */
    public function index(Request $request)
    {
        $this->authorize('suppliers.view');

        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 15);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;

        $query = Supplier::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.suppliers.index', compact('suppliers', 'search'));
    }

    /**
     * Show the form for creating a new supplier.
     */
    public function create()
    {
        $this->authorize('suppliers.create');

        return view('admin.suppliers.create');
    }

    /**
     * Store a newly created supplier in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('suppliers.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'ntn' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
        ]);

        $supplier = Supplier::create($validated);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit(Supplier $supplier)
    {
        $this->authorize('suppliers.update');

        return view('admin.suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $this->authorize('suppliers.update');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'ntn' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
        ]);

        $supplier->update($validated);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified supplier from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $this->authorize('suppliers.delete');

        $supplier->delete();

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    /**
     * Search suppliers (AJAX endpoint for purchase form)
     */
    public function search(Request $request)
    {
        $this->authorize('purchases.create');

        $term = $request->input('search', '');

        $suppliers = Supplier::active()
            ->when($term, function ($query) use ($term) {
                $query->search($term);
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        $suppliersData = $suppliers->map(function ($supplier) {
            return [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'company_name' => $supplier->company_name,
                'phone' => $supplier->phone,
                'email' => $supplier->email,
                'address' => $supplier->address,
            ];
        });

        return response()->json($suppliersData);
    }

    /**
     * Get all active suppliers (for purchase form initialization) - most recent first
     */
    public function getAll()
    {
        $this->authorize('purchases.create');

        $suppliers = Supplier::active()
            ->orderByDesc('id') // Most recent first
            ->limit(15) // Limit to 15 suppliers
            ->get();

        $suppliersData = $suppliers->map(function ($supplier) {
            return [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'company_name' => $supplier->company_name,
                'phone' => $supplier->phone,
                'email' => $supplier->email,
            ];
        });

        return response()->json($suppliersData);
    }

    /**
     * Create supplier via AJAX (for inline creation in purchase form)
     */
    public function storeAjax(Request $request)
    {
        $this->authorize('suppliers.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
        ]);

        $validated['status'] = Supplier::STATUS_ACTIVE;

        $supplier = Supplier::create($validated);

        return response()->json([
            'id' => $supplier->id,
            'name' => $supplier->name,
            'company_name' => $supplier->company_name,
            'phone' => $supplier->phone,
            'email' => $supplier->email,
            'message' => 'Supplier created successfully.',
        ]);
    }
}
