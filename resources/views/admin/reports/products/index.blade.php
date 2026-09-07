@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="page-title">
                    <i class="bi bi-box-seam"></i> Products Report
                </h1>
                <p class="text-muted small">View and manage all products in your inventory</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add New Product
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">Total Products</h6>
                    <h3 class="text-primary mb-0">{{ $totals['total_products'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">Active</h6>
                    <h3 class="text-success mb-0">{{ $totals['active_products'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">Avg. Margin</h6>
                    <h3 class="text-info mb-0">{{ number_format($totals['total_margin'], 1) }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">Avg. Sale Price</h6>
                    <h3 class="text-success mb-0">Rs. {{ number_format($totals['avg_sale_price'], 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-funnel"></i> Filters
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.products.index') }}" class="row g-3">
                <!-- Search -->
                <div class="col-md-4">
                    <label for="search" class="form-label small">Search by Name or SKU</label>
                    <input type="text" name="search" id="search" class="form-control form-control-sm" 
                           placeholder="Search..." value="{{ request('search') }}">
                </div>

                <!-- Status Filter -->
                <div class="col-md-3">
                    <label for="status" class="form-label small">Status</label>
                    <select name="status" id="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Unit Filter -->
                <div class="col-md-3">
                    <label for="unit" class="form-label small">Unit</label>
                    <select name="unit" id="unit" class="form-select form-select-sm">
                        <option value="">All Units</option>
                        @foreach($units as $value => $label)
                            <option value="{{ $value }}" {{ request('unit') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Message -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Products Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 30%;">
                            <a href="{{ route('admin.reports.products.index', array_merge(request()->query(), ['sort_by' => 'name', 'sort_order' => request('sort_order') === 'asc' && request('sort_by') === 'name' ? 'desc' : 'asc'])) }}" class="text-decoration-none">
                                Product Name
                                @if(request('sort_by') === 'name')
                                    <i class="bi bi-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th style="width: 10%;">SKU</th>
                        <th style="width: 10%;">Unit</th>
                        <th style="width: 15%;">
                            <a href="{{ route('admin.reports.products.index', array_merge(request()->query(), ['sort_by' => 'purchase_price', 'sort_order' => request('sort_order') === 'asc' && request('sort_by') === 'purchase_price' ? 'desc' : 'asc'])) }}" class="text-decoration-none">
                                Cost Price
                                @if(request('sort_by') === 'purchase_price')
                                    <i class="bi bi-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th style="width: 15%;">
                            <a href="{{ route('admin.reports.products.index', array_merge(request()->query(), ['sort_by' => 'sale_price', 'sort_order' => request('sort_order') === 'asc' && request('sort_by') === 'sale_price' ? 'desc' : 'asc'])) }}" class="text-decoration-none">
                                Sell Price
                                @if(request('sort_by') === 'sale_price')
                                    <i class="bi bi-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th style="width: 10%;">Margin</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 10%;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <strong>{{ $product->name }}</strong>
                            </td>
                            <td>
                                <small class="text-muted">{{ $product->sku ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $product->unit }}</span>
                            </td>
                            <td>
                                <strong>Rs. {{ number_format($product->purchase_price, 0) }}</strong>
                            </td>
                            <td>
                                <strong class="text-success">Rs. {{ number_format($product->sale_price, 0) }}</strong>
                            </td>
                            <td>
                                @if($product->purchase_price > 0)
                                    @php
                                        $margin = (($product->sale_price - $product->purchase_price) / $product->purchase_price) * 100;
                                    @endphp
                                    <span class="badge bg-{{ $margin >= 20 ? 'success' : ($margin >= 10 ? 'warning' : 'danger') }}">
                                        {{ number_format($margin, 1) }}%
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($product->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.products.edit', $product) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.reports.products.destroy', $product) }}" 
                                          method="POST" 
                                          style="display: inline;"
                                          onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox"></i> No products found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="row mt-4">
        <div class="col-md-6">
            <p class="text-muted small">
                Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products
            </p>
        </div>
        <div class="col-md-6 text-end">
            {{ $products->links() }}
        </div>
    </div>
</div>

@endsection
