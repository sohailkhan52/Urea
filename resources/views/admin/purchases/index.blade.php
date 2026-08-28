@extends('layouts.admin')

@section('title', 'Purchases')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Purchase Management</h1>
    </div>

    {{-- Search and Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.purchases.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by PO number or supplier">
                    </div>
                    <div class="col-md-2">
                        <label for="supplier_id" class="form-label">Supplier</label>
                        <select class="form-select" id="supplier_id" name="supplier_id">
                            <option value="">All Suppliers</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="warehouse_id" class="form-label">Warehouse</label>
                        <select class="form-select" id="warehouse_id" name="warehouse_id">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
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
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-secondary flex-grow-1">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Purchases Table --}}
    <div class="card">
        <div class="card-body">
            @if($purchases->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>PO Number</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Warehouse</th>
                            <th>Items</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-end">Paid</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 200px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchases as $purchase)
                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    <i class="bi bi-file-earmark-text me-1"></i>
                                    {{ $purchase->purchase_number }}
                                </div>
                            </td>
                            <td>
                                <small>{{ $purchase->purchase_date->format('M d, Y') }}</small>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $purchase->supplier->name }}</strong>
                                </div>
                                @if($purchase->supplier->company_name)
                                <small class="text-muted">{{ $purchase->supplier->company_name }}</small>
                                @endif
                            </td>
                            <td>
                                <small>
                                    <i class="bi bi-building me-1"></i>
                                    {{ $purchase->warehouse->name }}
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $purchase->items()->count() }} item(s)</span>
                            </td>
                            <td class="text-end">
                                <strong>{{ number_format($purchase->total_amount, 2) }}</strong>
                            </td>
                            <td class="text-end">
                                <small>
                                    @if($purchase->paid_amount > 0)
                                        <span class="text-success">{{ number_format($purchase->paid_amount, 2) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </small>
                            </td>
                            <td>
                                @if($purchase->isDraft())
                                    <span class="badge bg-warning">Draft</span>
                                @elseif($purchase->isConfirmed())
                                    <span class="badge bg-success">Confirmed</span>
                                @else
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    @can('purchases.view')
                                    <a href="{{ route('admin.purchases.show', $purchase) }}" 
                                       class="btn btn-info" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @endcan
                                    
                                    @if($purchase->isDraft())
                                        @can('purchases.update')
                                        <a href="{{ route('admin.purchases.edit', $purchase) }}" 
                                           class="btn btn-warning" 
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @endcan
                                    @endif
                                    
                                    @can('purchases.delete')
                                    <form action="{{ route('admin.purchases.destroy', $purchase) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-danger" 
                                                title="Delete"
                                                onclick="return confirm('Are you sure you want to delete this purchase? This action cannot be undone.');">
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
                {{ $purchases->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-file-earmark-text" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">
                    @if(request()->hasAny(['search', 'supplier_id', 'warehouse_id', 'status']))
                        No purchases found matching your criteria.
                    @else
                        No purchases yet. Create your first purchase order to get started.
                    @endif
                </p>
                @can('purchases.create')
                @if(!request()->hasAny(['search', 'supplier_id', 'warehouse_id', 'status']))
                <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Create Purchase
                </a>
                @endif
                @endcan
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
