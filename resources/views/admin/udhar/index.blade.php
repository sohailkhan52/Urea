@extends('layouts.admin')

@section('title', 'Customer Udhar Management')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Customer Udhar Management</h1>
                <p class="text-muted mb-0">Track outstanding payments from customers</p>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Customers</p>
                            <h3 class="mb-0">{{ $customersCount }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-people-fill fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Sales</p>
                            <h3 class="mb-0">{{ number_format($totalSales, 2) }}</h3>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-cart-fill fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Paid</p>
                            <h3 class="mb-0 text-success">{{ number_format($totalPaid, 2) }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-cash-coin fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Outstanding Udhar</p>
                            <h3 class="mb-0 text-danger">{{ number_format($totalUdhar, 2) }}</h3>
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.udhar.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search Customer</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ $filters['search'] ?? '' }}"
                               placeholder="Name or phone...">
                    </div>
                    <div class="col-md-2">
                        <label for="family_id" class="form-label">Family</label>
                        <select class="form-select" id="family_id" name="family_id">
                            <option value="">All Families</option>
                            @foreach($families as $family)
                            <option value="{{ $family->id }}" {{ ($filters['family_id'] ?? '') == $family->id ? 'selected' : '' }}>
                                {{ $family->name }} ({{ $family->family_code }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="warehouse_id" class="form-label">Warehouse</label>
                        <select class="form-select" id="warehouse_id" name="warehouse_id">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ ($filters['warehouse_id'] ?? '') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All</option>
                            <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block">Display</label>
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="only_outstanding" 
                                   name="only_outstanding" 
                                   value="1"
                                   {{ ($filters['only_outstanding'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="only_outstanding">
                                Only Outstanding
                            </label>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('admin.udhar.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Customer List --}}
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Customer Udhar List</h5>
        </div>
        <div class="card-body">
            @if($customersWithUdhar->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Customer</th>
                            <th>Mobile</th>
                            <th>Family</th>
                            <th>Warehouse</th>
                            <th class="text-end">Total Sales</th>
                            <th class="text-end">Total Paid</th>
                            <th class="text-end">Current Udhar</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 150px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customersWithUdhar as $item)
                        @php
                            $customer = $item['customer'];
                            $totalSales = $item['total_sales'];
                            $totalPaid = $item['total_paid'];
                            $currentUdhar = $item['current_udhar'];
                            $paymentStatus = $item['payment_status'];
                            
                            $statusBadge = match($paymentStatus) {
                                'paid' => 'success',
                                'partial' => 'warning',
                                'unpaid' => 'danger',
                                default => 'secondary',
                            };
                            
                            $statusLabel = match($paymentStatus) {
                                'paid' => 'Paid',
                                'partial' => 'Partial',
                                'unpaid' => 'Unpaid',
                                default => 'No Sales',
                            };
                        @endphp
                        <tr>
                            <td>
                                <div>
                                    <strong>{{ $customer->name }}</strong>
                                </div>
                                <small class="text-muted">{{ $customer->type_label }}</small>
                            </td>
                            <td>
                                @if($customer->phone)
                                    <small><i class="bi bi-telephone me-1"></i>{{ $customer->phone }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($customer->family)
                                    <div>
                                        <strong>{{ $customer->family->name }}</strong>
                                    </div>
                                    <small class="text-muted">{{ $customer->family->family_code }}</small>
                                @else
                                    <span class="text-muted">No Family</span>
                                @endif
                            </td>
                            <td>
                                <small>
                                    <i class="bi bi-building me-1"></i>
                                    {{ $customer->warehouse->name ?? 'N/A' }}
                                </small>
                            </td>
                            <td class="text-end">
                                <strong>{{ number_format($totalSales, 2) }}</strong>
                            </td>
                            <td class="text-end">
                                <span class="text-success">{{ number_format($totalPaid, 2) }}</span>
                            </td>
                            <td class="text-end">
                                @if($currentUdhar > 0)
                                    <strong class="text-danger">{{ number_format($currentUdhar, 2) }}</strong>
                                @else
                                    <span class="text-success">0.00</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $statusBadge }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.udhar.show', $customer) }}" 
                                       class="btn btn-info" 
                                       title="View Account">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.customers.statement', $customer) }}" 
                                       class="btn btn-secondary" 
                                       title="Statement">
                                        <i class="bi bi-file-text"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4" class="text-end">Totals:</th>
                            <th class="text-end">{{ number_format($totalSales, 2) }}</th>
                            <th class="text-end text-success">{{ number_format($totalPaid, 2) }}</th>
                            <th class="text-end text-danger">{{ number_format($totalUdhar, 2) }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                <p class="text-muted">
                    @if(request()->hasAny(['search', 'family_id', 'warehouse_id', 'status']))
                        No customers found matching your criteria.
                    @else
                        No customers with outstanding udhar.
                    @endif
                </p>
                @if(request()->hasAny(['search', 'family_id', 'warehouse_id', 'status']))
                <a href="{{ route('admin.udhar.index') }}" class="btn btn-outline-secondary">
                    Clear Filters
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>

    @if($families->count() > 0 && $customersWithUdhar->count() > 0)
    {{-- Family Summary (Optional Section) --}}
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Family-wise Udhar Summary</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Family</th>
                            <th>Members</th>
                            <th class="text-end">Total Udhar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $familyGroups = $customersWithUdhar->groupBy(function($item) {
                                return $item['customer']->family_id ?? 'no_family';
                            });
                        @endphp
                        @foreach($familyGroups as $familyId => $members)
                            @php
                                $firstMember = $members->first();
                                $family = $firstMember['customer']->family;
                                $familyUdhar = $members->sum('current_udhar');
                                $membersCount = $members->count();
                            @endphp
                            @if($familyUdhar > 0)
                            <tr>
                                <td>
                                    @if($family)
                                        <strong>{{ $family->name }}</strong>
                                        <small class="text-muted d-block">{{ $family->family_code }}</small>
                                    @else
                                        <span class="text-muted">No Family</span>
                                    @endif
                                </td>
                                <td>{{ $membersCount }} customer(s)</td>
                                <td class="text-end">
                                    <strong class="text-danger">{{ number_format($familyUdhar, 2) }}</strong>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="alert alert-info mt-3 mb-0">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Note:</strong> Each customer account is separate. Family grouping is for reference only.
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Optional: Add any interactive features here
    console.log('Udhar management page loaded');
});
</script>
@endpush
