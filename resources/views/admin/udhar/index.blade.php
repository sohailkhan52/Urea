@extends('layouts.admin')

@section('title', 'Udhar Management')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h3 mb-0">Udhar Management</h1>
        <p class="text-muted mb-0">Track individual customer and family outstanding balances</p>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <p class="text-muted mb-1 small">Total Accounts</p>
                    <h3 class="mb-0">{{ $accountsCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <p class="text-muted mb-1 small">Total Sales</p>
                    <h3 class="mb-0">Rs. {{ number_format($totalSales, 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <p class="text-muted mb-1 small">Total Paid</p>
                    <h3 class="mb-0 text-success">Rs. {{ number_format($totalPaid, 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <p class="text-muted mb-1 small">Outstanding Udhar</p>
                    <h3 class="mb-0 text-danger">Rs. {{ number_format($totalUdhar, 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'customers' ? 'active' : '' }}" 
               href="{{ route('admin.udhar.index', array_merge(request()->except('tab'), ['tab' => 'customers'])) }}">
                <i class="bi bi-person me-2"></i>Individual Customers
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'families' ? 'active' : '' }}" 
               href="{{ route('admin.udhar.index', array_merge(request()->except('tab'), ['tab' => 'families'])) }}">
                <i class="bi bi-diagram-3 me-2"></i>Family Accounts
            </a>
        </li>
    </ul>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.udhar.index') }}" method="GET">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small">Search</label>
                        <input type="text" class="form-control form-control-sm" name="search" 
                               value="{{ $filters['search'] ?? '' }}" 
                               placeholder="{{ $activeTab === 'families' ? 'Family name...' : 'Name or phone...' }}">
                    </div>
                    {{-- Show warehouse filter only if multiple warehouses exist --}}
                    @if($showWarehouseFilter)
                    <div class="col-md-2">
                        <label class="form-label small">Warehouse</label>
                        <select class="form-select form-select-sm" name="warehouse_id">
                            <option value="">All</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ ($filters['warehouse_id'] ?? '') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Display</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="only_outstanding" value="1"
                                   {{ ($filters['only_outstanding'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label small">Only Outstanding</label>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('admin.udhar.index', ['tab' => $activeTab]) }}" class="btn btn-outline-secondary btn-sm">
                            Clear
                        </a>
                    </div>
                    @else
                    {{-- Single warehouse - simplified filter --}}
                    <div class="col-md-2">
                        <label class="form-label small">Display</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="only_outstanding" value="1"
                                   {{ ($filters['only_outstanding'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label small">Only Outstanding</label>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('admin.udhar.index', ['tab' => $activeTab]) }}" class="btn btn-outline-secondary btn-sm">
                            Clear
                        </a>
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if($activeTab === 'families')
        {{-- FAMILIES VIEW --}}
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Family Accounts</h5>
            </div>
            <div class="card-body">
                @if($families->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Family</th>
                                <th class="text-center">Members</th>
                                <th class="text-end">Total Sales</th>
                                <th class="text-end">Total Paid</th>
                                <th class="text-end">Outstanding</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($families as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item['family']->name }}</strong>
                                    <br><small class="text-muted">{{ $item['family']->family_code }}</small>
                                </td>
                                <td class="text-center"><span class="badge bg-info">{{ $item['members_count'] }}</span></td>
                                <td class="text-end">Rs. {{ number_format($item['total_sales'], 0) }}</td>
                                <td class="text-end text-success">Rs. {{ number_format($item['total_paid'], 0) }}</td>
                                <td class="text-end">
                                    <strong class="text-danger">Rs. {{ number_format($item['outstanding'], 0) }}</strong>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.udhar.show-family', $item['family']) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $families->appends(request()->query())->links() }}</div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted">No family accounts found.</p>
                </div>
                @endif
            </div>
        </div>

    @else
        {{-- INDIVIDUAL CUSTOMERS VIEW --}}
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-person me-2"></i>Individual Customer Accounts</h5>
            </div>
            <div class="card-body">
                @if($customers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Phone</th>
                                @if($showWarehouseFilter)
                                <th>Warehouse</th>
                                @endif
                                <th class="text-end">Total Sales</th>
                                <th class="text-end">Total Paid</th>
                                <th class="text-end">Outstanding</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item['customer']->name }}</strong>
                                    <br><small class="text-muted">{{ $item['customer']->type_label }}</small>
                                </td>
                                <td><small>{{ $item['customer']->phone ?? '—' }}</small></td>
                                @if($showWarehouseFilter)
                                <td><small>{{ $item['customer']->warehouse->name ?? 'N/A' }}</small></td>
                                @endif
                                <td class="text-end">Rs. {{ number_format($item['total_sales'], 0) }}</td>
                                <td class="text-end text-success">Rs. {{ number_format($item['total_paid'], 0) }}</td>
                                <td class="text-end">
                                    <strong class="text-danger">Rs. {{ number_format($item['outstanding'], 0) }}</strong>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.udhar.show-customer', $item['customer']) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $customers->appends(request()->query())->links() }}</div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted">No individual customer accounts found.</p>
                </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
