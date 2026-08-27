@extends('layouts.admin')

@section('title', 'Customers')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Customer Management</h1>
    </div>

    {{-- Search and Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.customers.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by name, email, phone, CNIC, or village">
                    </div>
                    <div class="col-md-2">
                        <label for="customer_type" class="form-label">Type</label>
                        <select class="form-select" id="customer_type" name="customer_type">
                            <option value="">All Types</option>
                            <option value="farmer" {{ request('customer_type') === 'farmer' ? 'selected' : '' }}>Farmer</option>
                            <option value="dealer" {{ request('customer_type') === 'dealer' ? 'selected' : '' }}>Dealer</option>
                            <option value="retail_customer" {{ request('customer_type') === 'retail_customer' ? 'selected' : '' }}>Retail Customer</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="city" class="form-label">City</label>
                        <select class="form-select" id="city" name="city">
                            <option value="">All Cities</option>
                            @foreach($cities as $city)
                            <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>
                                {{ $city }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Customers Table --}}
    <div class="card">
        <div class="card-body">
            @if($customers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Phone</th>
                            <th>City</th>
                            <th>CNIC</th>
                            <th class="text-end">Credit Limit</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 200px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    <i class="bi bi-person me-1"></i>
                                    {{ $customer->name }}
                                </div>
                                @if($customer->father_name)
                                <small class="text-muted">S/O: {{ $customer->father_name }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $customer->type_badge }}">
                                    {{ $customer->type_label }}
                                </span>
                            </td>
                            <td>
                                @if($customer->phone)
                                <small>
                                    <i class="bi bi-telephone me-1"></i>
                                    {{ $customer->phone }}
                                </small>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($customer->city)
                                <small>
                                    <i class="bi bi-geo-alt me-1"></i>
                                    {{ $customer->city }}
                                </small>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($customer->cnic)
                                <code class="bg-light px-2 py-1 rounded">{{ $customer->cnic }}</code>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <strong>{{ number_format($customer->credit_limit, 2) }}</strong>
                            </td>
                            <td>
                                @if($customer->status === 'active')
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-warning">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    @can('customers.view')
                                    <a href="{{ route('admin.customers.show', $customer) }}" 
                                       class="btn btn-info" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('customers.update')
                                    <a href="{{ route('admin.customers.edit', $customer) }}" 
                                       class="btn btn-warning" 
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('customers.delete')
                                    <form action="{{ route('admin.customers.destroy', $customer) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $customers->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-person" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">
                    @if(request()->hasAny(['search', 'customer_type', 'city', 'status']))
                        No customers found matching your criteria.
                    @else
                        No customers yet. Create your first customer to get started.
                    @endif
                </p>
                @can('customers.create')
                @if(!request()->hasAny(['search', 'customer_type', 'city', 'status']))
                <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Add Customer
                </a>
                @endif
                @endcan
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
