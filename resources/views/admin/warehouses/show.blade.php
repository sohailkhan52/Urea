@extends('layouts.admin')

@section('title', 'Warehouse Details')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Warehouse Details</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">Warehouses</a></li>
                        <li class="breadcrumb-item active">{{ $warehouse->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                @can('warehouses.update')
                <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                @endcan
                <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Basic Information --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-1"></i> Basic Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Warehouse Name</label>
                            <p class="fw-semibold">{{ $warehouse->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Warehouse Code</label>
                            <p><code class="bg-light px-2 py-1 rounded">{{ $warehouse->code }}</code></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Type</label>
                            <p>
                                @if($warehouse->type === 'main_warehouse')
                                <span class="badge bg-primary">
                                    <i class="bi bi-building me-1"></i>{{ $warehouse->type_label }}
                                </span>
                                @elseif($warehouse->type === 'branch_warehouse')
                                <span class="badge bg-info">
                                    <i class="bi bi-shop me-1"></i>{{ $warehouse->type_label }}
                                </span>
                                @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-shop-window me-1"></i>{{ $warehouse->type_label }}
                                </span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Status</label>
                            <p>
                                @if($warehouse->status === 'active')
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-warning">Inactive</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Branch</label>
                            <p>
                                @if($warehouse->branch)
                                <i class="bi bi-geo-alt me-1"></i>
                                {{ $warehouse->branch->name }} ({{ $warehouse->branch->city }})
                                @else
                                <span class="text-muted">No branch assigned</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Manager</label>
                            <p>
                                @if($warehouse->manager)
                                <i class="bi bi-person me-1"></i>
                                {{ $warehouse->manager->name }}
                                <br>
                                <small class="text-muted">{{ $warehouse->manager->email }}</small>
                                @else
                                <span class="text-muted">Not assigned</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-muted small">Address</label>
                            <p>{{ $warehouse->address }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Inventory Summary --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-box-seam me-1"></i> Inventory Summary
                    </h5>
                    @can('warehouses.view')
                    <a href="{{ route('admin.warehouses.inventory', $warehouse) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye me-1"></i> View Full Inventory
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h3 class="mb-0 text-primary">{{ number_format($totalStock) }}</h3>
                                <p class="text-muted mb-0">Total Stock Quantity</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h3 class="mb-0 text-info">{{ $totalProductTypes }}</h3>
                                <p class="text-muted mb-0">Product Types</p>
                            </div>
                        </div>
                    </div>

                    @if($lowStockItems->count() > 0)
                    <div class="mt-3">
                        <div class="alert alert-warning mb-0">
                            <h6 class="alert-heading">
                                <i class="bi bi-exclamation-triangle me-1"></i> Low Stock Alert
                            </h6>
                            <p class="mb-0"><strong>{{ $lowStockItems->count() }}</strong> product(s) are below minimum stock level.</p>
                            <ul class="mb-0 mt-2">
                                @foreach($lowStockItems->take(5) as $item)
                                <li>
                                    {{ $item->product->name }} - 
                                    <strong>{{ $item->quantity }}</strong> 
                                    (Min: {{ $item->product->minimum_stock_level }})
                                </li>
                                @endforeach
                                @if($lowStockItems->count() > 5)
                                <li class="text-muted">
                                    And {{ $lowStockItems->count() - 5 }} more...
                                    <a href="{{ route('admin.warehouses.inventory', $warehouse) }}">View all</a>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Quick Actions --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning me-1"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @can('warehouses.view')
                        <a href="{{ route('admin.warehouses.inventory', $warehouse) }}" class="btn btn-outline-primary">
                            <i class="bi bi-box-seam me-1"></i> View Inventory
                        </a>
                        @endcan

                        @can('warehouses.update')
                        <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="btn btn-outline-warning">
                            <i class="bi bi-pencil me-1"></i> Edit Warehouse
                        </a>

                        @if($warehouse->status === 'inactive')
                        <form action="{{ route('admin.warehouses.activate', $warehouse) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-success w-100">
                                <i class="bi bi-check-circle me-1"></i> Activate Warehouse
                            </button>
                        </form>
                        @else
                        <form action="{{ route('admin.warehouses.deactivate', $warehouse) }}" 
                              method="POST"
                              onsubmit="return confirm('Are you sure you want to deactivate this warehouse?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-warning w-100">
                                <i class="bi bi-x-circle me-1"></i> Deactivate Warehouse
                            </button>
                        </form>
                        @endif
                        @endcan

                        @can('warehouses.delete')
                        <form action="{{ route('admin.warehouses.destroy', $warehouse) }}" 
                              method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this warehouse? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-trash me-1"></i> Delete Warehouse
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>

            {{-- Timestamps --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-1"></i> Timestamps
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Created</label>
                        <p class="mb-0">{{ $warehouse->created_at->format('M d, Y h:i A') }}</p>
                        <small class="text-muted">{{ $warehouse->created_at->diffForHumans() }}</small>
                    </div>
                    <div>
                        <label class="text-muted small">Last Updated</label>
                        <p class="mb-0">{{ $warehouse->updated_at->format('M d, Y h:i A') }}</p>
                        <small class="text-muted">{{ $warehouse->updated_at->diffForHumans() }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
