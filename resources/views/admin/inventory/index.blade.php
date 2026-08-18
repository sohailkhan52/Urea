@extends('layouts.admin')

@section('title', 'Inventory Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-box-seam me-2"></i> Inventory Dashboard
        </h1>
        @can('inventory.view')
        <a href="{{ route('admin.inventory.low-stock') }}" class="btn btn-warning">
            <i class="bi bi-exclamation-triangle me-1"></i> Low Stock Items
        </a>
        @endcan
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Items</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_items']) }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-box-seam" style="font-size: 2rem;"></i>
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
                            <h6 class="text-muted mb-1">Total Quantity</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_quantity']) }}</h3>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-boxes" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Low Stock</h6>
                            <h3 class="mb-0">{{ number_format($stats['low_stock']) }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
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
                            <h6 class="text-muted mb-1">Out of Stock</h6>
                            <h3 class="mb-0">{{ number_format($stats['out_of_stock']) }}</h3>
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-x-circle" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search and Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.inventory.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search product or warehouse">
                    </div>
                    <div class="col-md-3">
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
                    <div class="col-md-3">
                        <label for="stock_status" class="form-label">Stock Status</label>
                        <select class="form-select" id="stock_status" name="stock_status">
                            <option value="">All Status</option>
                            <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                            <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                            <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-secondary">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Inventory Table --}}
    <div class="card">
        <div class="card-body">
            @if($inventory->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Warehouse</th>
                            <th>Company</th>
                            <th>Category</th>
                            <th class="text-end">Current Stock</th>
                            <th class="text-end">Min. Level</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 100px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inventory as $item)
                        <tr class="{{ $item->isLowStock() ? 'table-warning' : '' }}">
                            <td>
                                <img src="{{ $item->product->image_url }}" 
                                     alt="{{ $item->product->name }}" 
                                     class="rounded"
                                     style="width: 40px; height: 40px; object-fit: cover;">
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $item->product->name }}</div>
                                <small class="text-muted">{{ $item->product->weight_display }}</small>
                            </td>
                            <td>
                                <code class="bg-light px-2 py-1 rounded">{{ $item->product->sku }}</code>
                            </td>
                            <td>
                                <small>
                                    <i class="bi bi-house-door me-1"></i>
                                    {{ $item->warehouse->name }}
                                </small>
                            </td>
                            <td>
                                <small>{{ $item->product->company->name }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $item->product->category->name }}</span>
                            </td>
                            <td class="text-end">
                                <strong class="fs-5">{{ number_format($item->quantity, 2) }}</strong>
                                <small class="text-muted d-block">{{ $item->product->weight_unit }}</small>
                            </td>
                            <td class="text-end">
                                <small class="text-muted">{{ number_format($item->product->minimum_stock_level) }}</small>
                            </td>
                            <td>
                                @if($item->quantity == 0)
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle me-1"></i>Out of Stock
                                </span>
                                @elseif($item->isLowStock())
                                <span class="badge bg-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Low Stock
                                </span>
                                @else
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>In Stock
                                </span>
                                @endif
                            </td>
                            <td class="text-end">
                                @can('inventory.view')
                                <a href="{{ route('admin.inventory.movements', ['warehouse_id' => $item->warehouse_id, 'product_id' => $item->product_id]) }}" 
                                   class="btn btn-sm btn-info"
                                   title="View Stock Movements and History">
                                    <i class="bi bi-clock-history me-1"></i>History
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $inventory->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-box-seam" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">
                    @if(request()->hasAny(['search', 'warehouse_id', 'stock_status']))
                        No inventory items found matching your criteria.
                    @else
                        No inventory items yet. Add stock through stock movements.
                    @endif
                </p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
