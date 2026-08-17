@extends('layouts.admin')

@section('title', $product->name)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h3 mb-0">Product Details</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
                <li class="breadcrumb-item active">{{ $product->name }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Product Profile --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img src="{{ $product->image_url }}" 
                                 alt="{{ $product->name }}" 
                                 class="img-fluid rounded"
                                 style="max-height: 200px; object-fit: contain;">
                        </div>
                        <div class="col-md-8">
                            <h3 class="mb-2">{{ $product->name }}</h3>
                            <p class="text-muted mb-3">
                                <code class="bg-light px-3 py-1 rounded">{{ $product->sku }}</code>
                                @if($product->barcode)
                                <code class="bg-light px-3 py-1 rounded ms-2">
                                    <i class="bi bi-upc-scan"></i> {{ $product->barcode }}
                                </code>
                                @endif
                            </p>

                            <div class="mb-3">
                                @if($product->status === 'active')
                                <span class="badge bg-success me-2">Active</span>
                                @else
                                <span class="badge bg-warning me-2">Inactive</span>
                                @endif
                                <span class="badge bg-secondary">{{ $product->category->name }}</span>
                            </div>

                            <p class="text-muted mb-2">
                                <i class="bi bi-building me-1"></i>
                                <strong>Company:</strong> {{ $product->company->name }}
                            </p>

                            <p class="text-muted mb-2">
                                <i class="bi bi-box me-1"></i>
                                <strong>Weight:</strong> {{ $product->weight_display }}
                            </p>

                            @if($product->description)
                            <p class="mt-3">{{ $product->description }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    <div class="mt-4 d-flex gap-2">
                        @can('products.update')
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>

                        @if($product->status === 'inactive')
                        <form action="{{ route('admin.products.activate', $product) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i> Activate
                            </button>
                        </form>
                        @else
                        <form action="{{ route('admin.products.deactivate', $product) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('Are you sure you want to deactivate this product?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-warning">
                                <i class="bi bi-x-circle me-1"></i> Deactivate
                            </button>
                        </form>
                        @endif
                        @endcan

                        @can('products.delete')
                        <form action="{{ route('admin.products.destroy', $product) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash me-1"></i> Delete
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>

            {{-- Pricing Information --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-currency-rupee me-1"></i> Pricing Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <small class="text-muted d-block">Purchase Price</small>
                                <h4 class="mb-0">{{ $product->formatted_purchase_price }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded bg-light">
                                <small class="text-muted d-block">Sale Price</small>
                                <h4 class="mb-0 text-primary">{{ $product->formatted_sale_price }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <small class="text-muted d-block">Profit Margin</small>
                                <h4 class="mb-0 text-success">{{ number_format($product->profit_margin, 2) }}%</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stock Information --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-box-seam me-1"></i> Stock Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">Current Stock:</span>
                                <strong>{{ $product->getCurrentStock() }} bags</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">Minimum Level:</span>
                                <strong>{{ $product->minimum_stock_level }} bags</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Status:</span>
                                @if($product->getCurrentStock() >= $product->minimum_stock_level)
                                <span class="badge bg-success">In Stock</span>
                                @elseif($product->getCurrentStock() > 0)
                                <span class="badge bg-warning">Low Stock</span>
                                @else
                                <span class="badge bg-danger">Out of Stock</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                <small>
                                    Stock is calculated from warehouse inventory and managed through purchases, sales, and stock movements.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Product Information --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-1"></i> Product Information
                    </h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 small">SKU:</dt>
                        <dd class="col-sm-7 small">
                            <code class="bg-light px-2 py-1 rounded">{{ $product->sku }}</code>
                        </dd>

                        @if($product->barcode)
                        <dt class="col-sm-5 small">Barcode:</dt>
                        <dd class="col-sm-7 small">
                            <code class="bg-light px-2 py-1 rounded">{{ $product->barcode }}</code>
                        </dd>
                        @endif

                        <dt class="col-sm-5 small">Company:</dt>
                        <dd class="col-sm-7 small">{{ $product->company->name }}</dd>

                        <dt class="col-sm-5 small">Category:</dt>
                        <dd class="col-sm-7 small">
                            <span class="badge bg-secondary">{{ $product->category->name }}</span>
                        </dd>

                        <dt class="col-sm-5 small">Bag Weight:</dt>
                        <dd class="col-sm-7 small">{{ $product->weight_display }}</dd>

                        <dt class="col-sm-5 small">Status:</dt>
                        <dd class="col-sm-7 small">
                            @if($product->status === 'active')
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-warning">Inactive</span>
                            @endif
                        </dd>

                        <dt class="col-sm-5 small">Created:</dt>
                        <dd class="col-sm-7 small text-muted">
                            {{ $product->created_at->format('M d, Y') }}
                            <br>
                            <span class="text-muted">{{ $product->created_at->diffForHumans() }}</span>
                        </dd>

                        <dt class="col-sm-5 small">Last Updated:</dt>
                        <dd class="col-sm-7 small text-muted">
                            {{ $product->updated_at->format('M d, Y') }}
                            <br>
                            <span class="text-muted">{{ $product->updated_at->diffForHumans() }}</span>
                        </dd>
                    </dl>
                </div>
            </div>

            {{-- Related Data (Placeholder for future) --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-diagram-3 me-1"></i> Related Data
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Total Purchases</span>
                        <span class="badge bg-secondary">0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Total Sales</span>
                        <span class="badge bg-secondary">0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Stock Movements</span>
                        <span class="badge bg-secondary">0</span>
                    </div>
                    <hr>
                    <p class="small text-muted mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Related data will be available when warehouse, purchase, and sales modules are implemented.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
