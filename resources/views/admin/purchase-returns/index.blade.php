@extends('layouts.admin')

@section('title', 'Purchase Returns')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Purchase Returns</h1>
        @can('purchases.create')
        <a href="{{ route('admin.purchases.returns.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add Return
        </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.purchases.returns.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}"
                               placeholder="Return # or PO #">
                    </div>
                    <div class="col-md-2">
                        <label for="warehouse" class="form-label">Warehouse</label>
                        <select class="form-select" id="warehouse" name="warehouse">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="supplier" class="form-label">Supplier</label>
                        <select class="form-select" id="supplier" name="supplier">
                            <option value="">All Suppliers</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.purchases.returns.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Returns Table --}}
    <div class="card">
        <div class="card-body">
            @if($returns->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Return #</th>
                            <th>Date</th>
                            <th>PO #</th>
                            <th>Supplier</th>
                            <th>Return Type</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($returns as $return)
                        <tr>
                            <td>
                                <a href="{{ route('admin.purchases.returns.show', $return) }}" class="text-decoration-none">
                                    <strong>{{ $return->return_number }}</strong>
                                </a>
                            </td>
                            <td>{{ $return->return_date->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.purchases.show', $return->purchase) }}">
                                    {{ $return->purchase->purchase_number }}
                                </a>
                            </td>
                            <td>{{ $return->purchase->supplier->name }}</td>
                            <td>
                                @if($return->return_type === 'WHOLE_ORDER')
                                    <span class="badge bg-primary">Whole Order</span>
                                @else
                                    <span class="badge bg-secondary">Partial Items</span>
                                @endif
                            </td>
                            <td class="text-end">Rs. {{ number_format($return->total_amount, 2) }}</td>
                            <td>
                                @if($return->status === 'draft')
                                    <span class="badge bg-warning">Draft</span>
                                @elseif($return->status === 'confirmed')
                                    <span class="badge bg-success">Confirmed</span>
                                @elseif($return->status === 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.purchases.returns.show', $return) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($returns->hasPages())
            <div class="mt-4">
                {{ $returns->links() }}
            </div>
            @endif
            @else
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle me-2"></i>
                No purchase returns found. <a href="{{ route('admin.purchases.returns.create') }}">Create one now</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
