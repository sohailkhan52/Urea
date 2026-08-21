@extends('layouts.admin')

@section('title', 'Udhar Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Udhar Management</h1>
        <a href="{{ route('admin.udhar.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Customers with Udhar</h6>
                            <h3 class="mb-0">{{ $totalCustomersWithUdhar }}</h3>
                        </div>
                        <div class="ms-auto">
                            <i class="bi bi-people-fill text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Outstanding Udhar</h6>
                            <h3 class="mb-0">Rs. {{ number_format($grandTotalUdhar, 2) }}</h3>
                        </div>
                        <div class="ms-auto">
                            <i class="bi bi-cash-coin text-warning" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Partial Invoices</h6>
                            <h3 class="mb-0">{{ $partialInvoices }}</h3>
                        </div>
                        <div class="ms-auto">
                            <i class="bi bi-receipt text-info" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Unpaid Invoices</h6>
                            <h3 class="mb-0">{{ $unpaidInvoices }}</h3>
                        </div>
                        <div class="ms-auto">
                            <i class="bi bi-exclamation-circle-fill text-danger" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search and Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.udhar.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by name or phone">
                    </div>
                    <div class="col-md-2">
                        <label for="customer_type" class="form-label">Customer Type</label>
                        <select class="form-select" id="customer_type" name="customer_type">
                            <option value="">All Types</option>
                            @foreach(\App\Models\Customer::$types as $key => $label)
                            <option value="{{ $key }}" {{ request('customer_type') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="udhar_min" class="form-label">Min Udhar (Rs.)</label>
                        <input type="number" 
                               class="form-control" 
                               id="udhar_min" 
                               name="udhar_min" 
                               value="{{ request('udhar_min') }}"
                               placeholder="Minimum">
                    </div>
                    <div class="col-md-2">
                        <label for="udhar_max" class="form-label">Max Udhar (Rs.)</label>
                        <input type="number" 
                               class="form-control" 
                               id="udhar_max" 
                               name="udhar_max" 
                               value="{{ request('udhar_max') }}"
                               placeholder="Maximum">
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_from" 
                               name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_to" 
                               name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-8 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.udhar.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Customers with Udhar Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Customer Name</th>
                        <th>Type</th>
                        <th>Phone</th>
                        <th class="text-end">Total Sales</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Total Paid</th>
                        <th class="text-end">Total Udhar</th>
                        <th>Last Payment</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td>
                            <strong>{{ $customer['name'] }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-{{ $customer['type_badge'] }}">
                                {{ $customer['customer_type_label'] }}
                            </span>
                        </td>
                        <td>{{ $customer['phone'] ?? 'N/A' }}</td>
                        <td class="text-end">{{ $customer['total_sales'] }}</td>
                        <td class="text-end">Rs. {{ number_format($customer['total_amount'], 2) }}</td>
                        <td class="text-end">Rs. {{ number_format($customer['total_paid'], 2) }}</td>
                        <td class="text-end">
                            <strong class="text-danger">
                                Rs. {{ number_format($customer['total_udhar'], 2) }}
                            </strong>
                        </td>
                        <td>{{ $customer['last_payment_date'] }}</td>
                        <td>
                            <span class="badge bg-{{ $customer['status_badge'] }}">
                                {{ $customer['status_label'] }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.udhar.details', $customer['id']) }}" 
                                   class="btn btn-outline-primary" 
                                   title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.udhar.ledger', $customer['id']) }}" 
                                   class="btn btn-outline-info" 
                                   title="View Ledger">
                                    <i class="bi bi-book"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 2rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">No customers with outstanding Udhar found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="card-footer bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} 
                    of {{ $customers->total() }} customers
                </small>
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
