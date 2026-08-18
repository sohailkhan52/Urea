@extends('layouts.admin')

@section('title', 'Warehouse Inventory')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Warehouse Inventory</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">Warehouses</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.show', $warehouse) }}">{{ $warehouse->name }}</a></li>
                        <li class="breadcrumb-item active">Inventory</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Warehouse
            </a>
        </div>
    </div>

    {{-- Warehouse Info --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-2">
                        <i class="bi bi-house-door me-1"></i> {{ $warehouse->name }}
                    </h5>
                    <p class="mb-0 text-muted">
                        <code class="bg-light px-2 py-1 rounded me-2">{{ $warehouse->code }}</code>
                        @if($warehouse->branch)
                        <span class="me-2">
                            <i class="bi bi-geo-alt me-1"></i>{{ $warehouse->branch->name }} - {{ $warehouse->branch->city }}
                        </span>
                        @endif
                        @if($warehouse->manager)
                        <span>
                            <i class="bi bi-person me-1"></i>{{ $warehouse->manager->name }}
                        </span>
                        @endif
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="badge bg-{{ $warehouse->status === 'active' ? 'success' : 'warning' }} fs-6">
                        {{ ucfirst($warehouse->status) }}
                    </div>
                </div>
            </div>
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
                            <th>Company</th>
                            <th>Category</th>
                            <th>Weight</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Min. Level</th>
                            <th style="width: 100px;">Status</th>
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
                                @if($item->product->barcode)
                                <small class="text-muted">
                                    <i class="bi bi-upc-scan"></i> {{ $item->product->barcode }}
                                </small>
                                @endif
                            </td>
                            <td>
                                <code class="bg-light px-2 py-1 rounded">{{ $item->product->sku }}</code>
                            </td>
                            <td>
                                <small>{{ $item->product->company->name }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $item->product->category->name }}</span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $item->product->weight_display }}</small>
                            </td>
                            <td class="text-end">
                                <strong class="fs-5">{{ number_format($item->quantity) }}</strong>
                                <small class="text-muted d-block">{{ $item->product->weight_unit }}</small>
                            </td>
                            <td class="text-end">
                                <small class="text-muted">{{ number_format($item->product->minimum_stock_level) }}</small>
                            </td>
                            <td>
                                @if($item->quantity == 0)
                                <span class="badge bg-danger">Out of Stock</span>
                                @elseif($item->isLowStock())
                                <span class="badge bg-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Low Stock
                                </span>
                                @else
                                <span class="badge bg-success">In Stock</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="6" class="text-end"><strong>Total Quantity:</strong></td>
                            <td class="text-end">
                                <strong class="fs-5">{{ number_format($inventory->sum('quantity')) }}</strong>
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $inventory->links() }}
            </div>

            {{-- Legend --}}
            <div class="mt-3 p-3 bg-light rounded">
                <h6 class="mb-2">
                    <i class="bi bi-info-circle me-1"></i> Inventory Status Legend
                </h6>
                <div class="row g-2 small">
                    <div class="col-md-4">
                        <span class="badge bg-success me-1">In Stock</span>
                        Quantity above minimum level
                    </div>
                    <div class="col-md-4">
                        <span class="badge bg-warning me-1">Low Stock</span>
                        Quantity below minimum level
                    </div>
                    <div class="col-md-4">
                        <span class="badge bg-danger me-1">Out of Stock</span>
                        No quantity available
                    </div>
                </div>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-box-seam" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">
                    No inventory items found in this warehouse.
                </p>
                <p class="text-muted">
                    Inventory will be managed through purchase orders and stock movements.
                </p>
            </div>
            @endif
        </div>
    </div>

    {{-- Summary Cards --}}
    @if($inventory->count() > 0)
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ $inStock }}</h3>
                    <p class="text-muted mb-0">Products In Stock</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ $lowStock }}</h3>
                    <p class="text-muted mb-0">Low Stock Items</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <i class="bi bi-x-circle text-danger" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ $outOfStock }}</h3>
                    <p class="text-muted mb-0">Out of Stock</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
