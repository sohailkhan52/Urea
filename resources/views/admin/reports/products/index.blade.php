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
                <a href="{{ route('admin.reports.products.history') }}" class="btn btn-outline-primary me-2">
                    <i class="bi bi-clock-history"></i> Product History
                </a>
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
                    <h3 class="text-info mb-0">Rs. {{ number_format($totals['total_margin'], 0) }}</h3>
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
                                        $margin = $product->sale_price - $product->purchase_price;
                                    @endphp
                                    <strong class="text-{{ $margin >= 0 ? 'success' : 'danger' }}">Rs. {{ number_format($margin, 0) }}</strong>
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
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Edit"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editProductModal"
                                                    data-update-url="{{ route('admin.products.update', $product) }}"
                                                    data-name="{{ $product->name }}"
                                                    data-unit="{{ $product->unit }}"
                                                    data-purchase-price="{{ $product->purchase_price }}"
                                                    data-sale-price="{{ $product->sale_price }}"
                                                    data-minimum-stock-level="{{ $product->minimum_stock_level ?? 10 }}">
                                        <i class="bi bi-pencil"></i>
                                                </button>
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

<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editProductForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-product-name" class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" id="edit-product-name" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-product-unit" class="form-label">Unit <span class="text-danger">*</span></label>
                        <select id="edit-product-unit" name="unit" class="form-select" required>
                            @foreach($units as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-product-purchase-price" class="form-label">Purchase Price (Rs.) <span class="text-danger">*</span></label>
                        <input type="number" id="edit-product-purchase-price" name="purchase_price" class="form-control" min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-product-sale-price" class="form-label">Sale Price (Rs.) <span class="text-danger">*</span></label>
                        <input type="number" id="edit-product-sale-price" name="sale_price" class="form-control" min="0" step="0.01" required>
                    </div>
                    <div>
                        <label for="edit-product-minimum-stock-level" class="form-label">Minimum Stock Level</label>
                        <input type="number" id="edit-product-minimum-stock-level" name="minimum_stock_level" class="form-control" min="0" step="1">
                        <small class="text-muted">Alert will show when stock falls below this level</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.getElementById('editProductModal')?.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const form = document.getElementById('editProductForm');

        form.action = button.dataset.updateUrl;
        document.getElementById('edit-product-name').value = button.dataset.name;
        document.getElementById('edit-product-unit').value = button.dataset.unit;
        document.getElementById('edit-product-purchase-price').value = button.dataset.purchasePrice;
        document.getElementById('edit-product-sale-price').value = button.dataset.salePrice;
        document.getElementById('edit-product-minimum-stock-level').value = button.dataset.minimumStockLevel;
    });
</script>
@endpush
