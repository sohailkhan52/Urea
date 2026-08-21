<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request): View
    {
        $this->authorize('customers.view');

        $user = auth()->user();
        $query = Customer::query();

        // Filter customers by warehouse for non-super-admins
        if (!$user->isSuperAdmin()) {
            $userWarehouses = $user->warehouses()
                ->select('warehouses.id')
                ->pluck('warehouses.id');

            if ($userWarehouses->isEmpty()) {
                // User has no warehouse assigned, show no customers
                $customers = collect();
                $cities = collect();
                
                return view('admin.customers.index', compact('customers', 'cities'));
            }

            // Only show customers from user's assigned warehouse
            $query->whereIn('warehouse_id', $userWarehouses);
        }

        // Search by name, email, phone, cnic, or village
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('cnic', 'like', "%{$search}%")
                    ->orWhere('village', 'like', "%{$search}%");
            });
        }

        // Filter by customer type
        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $customers = $query->latest()->paginate(15)->withQueryString();

        // Get unique cities for filter - create a fresh query for this
        $citiesQuery = Customer::query();
        
        if (!$user->isSuperAdmin()) {
            $userWarehouses = $user->warehouses()
                ->select('warehouses.id')
                ->pluck('warehouses.id');
            $citiesQuery->whereIn('warehouse_id', $userWarehouses);
        }
        
        $cities = $citiesQuery->whereNotNull('city')
            ->distinct()
            ->pluck('city')
            ->sort();

        return view('admin.customers.index', compact('customers', 'cities'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): View
    {
        $this->authorize('customers.create');

        $types = Customer::$types;
        
        // Get warehouses for super admins, or just the current warehouse for regular admins
        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            $warehouses = Warehouse::active()->orderBy('name')->get();
        } else {
            $warehouses = $user->warehouses()->active()->orderBy('name')->get();
        }

        return view('admin.customers.create', compact('types', 'warehouses'));
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $this->authorize('customers.create');

        try {
            $data = $request->validated();

            // Auto-assign customer to user's warehouse if not super admin
            if (!auth()->user()->isSuperAdmin()) {
                $userWarehouse = auth()->user()->warehouses()->first();
                if (!$userWarehouse) {
                    return back()->with('error', 'You are not assigned to any warehouse.');
                }
                $data['warehouse_id'] = $userWarehouse->id;
            }

            $customer = Customer::create($data);

            // Dispatch CustomerCreated event to trigger welcome email
            \App\Events\CustomerCreated::dispatch($customer);

            return redirect()->route('admin.customers.show', $customer)
                ->with('success', 'Customer created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error creating customer: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): View
    {
        $this->authorize('customers.view');

        // Check warehouse access for non-super-admins
        if (!auth()->user()->isSuperAdmin() && $customer->warehouse_id && !auth()->user()->canAccessWarehouse($customer->warehouse_id)) {
            abort(403, 'You do not have access to this customer.');
        }

        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): View
    {
        $this->authorize('customers.update');

        // Check warehouse access for non-super-admins
        if (!auth()->user()->isSuperAdmin() && $customer->warehouse_id && !auth()->user()->canAccessWarehouse($customer->warehouse_id)) {
            abort(403, 'You do not have access to this customer.');
        }

        $types = Customer::$types;
        
        // Get warehouses for super admins, or just the current warehouse for regular admins
        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            $warehouses = Warehouse::active()->orderBy('name')->get();
        } else {
            $warehouses = $user->warehouses()->active()->orderBy('name')->get();
        }

        return view('admin.customers.edit', compact('customer', 'types', 'warehouses'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->authorize('customers.update');

        try {
            $customer->update($request->validated());

            return redirect()->route('admin.customers.show', $customer)
                ->with('success', 'Customer updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating customer: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('customers.delete');

        // Check warehouse access for non-super-admins
        if (!auth()->user()->isSuperAdmin() && $customer->warehouse_id && !auth()->user()->canAccessWarehouse($customer->warehouse_id)) {
            abort(403, 'You do not have access to this customer.');
        }

        try {
            if (!$customer->canBeDeleted()) {
                return back()->with('error', 'This customer has associated transactions and cannot be deleted.');
            }

            $customer->delete();

            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting customer: ' . $e->getMessage());
        }
    }

    /**
     * Activate a customer.
     */
    public function activate(Customer $customer): RedirectResponse
    {
        $this->authorize('customers.update');

        // Check warehouse access for non-super-admins
        if (!auth()->user()->isSuperAdmin() && $customer->warehouse_id && !auth()->user()->canAccessWarehouse($customer->warehouse_id)) {
            abort(403, 'You do not have access to this customer.');
        }

        try {
            $customer->update(['status' => Customer::STATUS_ACTIVE]);

            return back()->with('success', 'Customer activated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error activating customer: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate a customer.
     */
    public function deactivate(Customer $customer): RedirectResponse
    {
        $this->authorize('customers.update');

        // Check warehouse access for non-super-admins
        if (!auth()->user()->isSuperAdmin() && $customer->warehouse_id && !auth()->user()->canAccessWarehouse($customer->warehouse_id)) {
            abort(403, 'You do not have access to this customer.');
        }

        try {
            $customer->update(['status' => Customer::STATUS_INACTIVE]);

            return back()->with('success', 'Customer deactivated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deactivating customer: ' . $e->getMessage());
        }
    }
}
