<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Family;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('customers.view');

        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 15);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;

        $customers = Customer::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('village', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $families = Family::active()->orderBy('name')->get();

        return view('admin.customers.index', compact('customers', 'search', 'families'));
    }

    public function store(Request $request)
    {
        $this->authorize('customers.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'customer_type' => 'required|in:farmer,dealer,retail_customer',
            'family_id' => 'nullable|exists:families,id',
            'father_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'village' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ]);

        Customer::create($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorize('customers.update');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'customer_type' => 'required|in:farmer,dealer,retail_customer',
            'family_id' => 'nullable|exists:families,id',
            'father_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'village' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ]);

        $customer->update($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('customers.delete');

        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
