@extends('layouts.admin')

@section('content')
<style>
    @media (min-width: 576px) and (max-width: 991.98px) {
        .products-report-page {
            padding-top: 1rem;
        }

        .products-report-heading {
            margin-bottom: 1rem !important;
        }

        .products-report-heading > .row {
            align-items: center !important;
        }

        .products-report-heading .col-md-8 {
            flex: 1 1 280px;
            width: auto;
            max-width: none;
        }

        .products-report-heading .col-md-4 {
            flex: 0 1 auto;
            width: auto;
            max-width: 100%;
        }

        .products-report-heading .page-title {
            font-size: clamp(1.5rem, 3vw, 1.9rem);
        }

        .products-report-heading p {
            margin-bottom: .25rem;
        }

        .products-report-actions {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: .5rem;
            margin-top: 0;
            text-align: right;
        }

        .products-report-actions .btn {
            margin: 0 !important;
            padding: .45rem .65rem;
            white-space: nowrap;
        }

        .products-report-summary > div {
            flex: 0 0 50%;
            width: 50%;
            max-width: 50%;
            margin-bottom: .75rem;
        }

        .products-report-filters .col-md-4,
        .products-report-filters .col-md-3,
        .products-report-filters .col-md-2 {
            flex: 0 0 50%;
            width: 50%;
            max-width: 50%;
        }

        .products-report-form-modal-dialog {
            width: calc(100% - 2rem);
            max-width: 540px;
        }

        .products-report-form-modal .modal-content {
            max-height: calc(100dvh - 2rem);
            overflow: hidden;
        }

        .products-report-form-modal form {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
            min-height: 0;
        }

        .products-report-form-modal .modal-header,
        .products-report-form-modal .modal-footer {
            flex-shrink: 0;
            padding: .7rem 1rem;
        }

        .products-report-form-modal .modal-title {
            font-size: 1.1rem;
        }

        .products-report-form-modal .modal-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding: .75rem 1rem;
        }

        .products-report-form-modal .modal-body .mb-3 {
            margin-bottom: .65rem !important;
        }

        .products-report-form-modal .form-label {
            margin-bottom: .25rem;
            font-size: .9rem;
        }

        .products-report-form-modal .form-control,
        .products-report-form-modal .form-select {
            min-height: 40px;
            padding: .35rem .6rem;
            font-size: .9rem;
        }
    }

    @media (max-width: 575.98px) {
        .products-report-page {
            padding-top: .75rem;
        }

        .products-report-heading {
            margin-bottom: .85rem !important;
        }

        .products-report-heading .page-title {
            margin-bottom: 0;
            font-size: 1.35rem;
            white-space: nowrap;
        }

        .products-report-heading p {
            display: none;
        }

        .products-report-actions {
            display: flex;
            flex: 0 0 100%;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: .35rem;
            margin-top: .45rem;
        }

        .products-report-actions .btn {
            margin: 0 !important;
            padding: .34rem .48rem;
            font-size: .74rem;
            line-height: 1.25;
            white-space: nowrap;
        }

        .products-report-summary {
            margin-bottom: .75rem !important;
        }

        .products-report-summary > div {
            margin-bottom: .7rem;
        }

        .products-report-summary .card {
            width: 100%;
            margin: 0 auto;
        }

        .products-report-summary .card-body {
            padding: .8rem .65rem;
        }

        .products-report-summary .card-title {
            font-size: .82rem;
        }

        .products-report-summary h3 {
            font-size: clamp(.78rem, 3.6vw, 1.15rem);
            overflow-wrap: anywhere;
        }

        .products-report-summary .products-report-amount {
            font-size: clamp(.62rem, 3vw, .9rem);
            letter-spacing: -.035em;
            white-space: nowrap;
        }

        .products-report-filters {
            margin-bottom: .8rem !important;
        }

        .products-report-filters .card-header,
        .products-report-filters .card-body {
            padding: .5rem .6rem;
        }

        .products-report-filters .card-header h5 {
            font-size: .95rem;
        }

        .products-report-filters .row {
            --bs-gutter-x: .5rem;
            --bs-gutter-y: .45rem;
        }

        .products-report-filters .form-label {
            margin-bottom: .2rem;
            font-size: .76rem !important;
        }

        .products-report-filters .form-control,
        .products-report-filters .form-select {
            min-height: 34px;
            padding: .28rem .45rem;
            font-size: .8rem;
        }

        .products-report-filters button {
            min-height: 34px;
            padding: .3rem .45rem;
            font-size: .76rem;
        }

        .products-report-per-page {
            margin-bottom: .45rem !important;
        }

        .products-report-per-page form {
            gap: .35rem !important;
        }

        .products-report-per-page label {
            font-size: .76rem;
        }

        .products-report-per-page select {
            min-height: 32px;
            padding: .22rem 1.6rem .22rem .45rem;
            font-size: .8rem;
        }

        .products-report-table table {
            min-width: 760px;
        }

        .products-report-pagination {
            margin-top: .8rem !important;
            overflow-x: auto;
        }

        .products-report-form-modal-dialog {
            width: calc(100% - 2rem);
            max-width: none;
            margin: 1rem auto;
        }

        .products-report-form-modal .modal-content {
            max-height: calc(100dvh - 2rem);
            overflow: hidden;
        }

        .products-report-form-modal form {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
            min-height: 0;
        }

        .products-report-form-modal .modal-header,
        .products-report-form-modal .modal-footer {
            flex-shrink: 0;
            padding: .55rem .75rem;
        }

        .products-report-form-modal .modal-title {
            font-size: .95rem;
        }

        .products-report-form-modal .modal-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding: .6rem .75rem;
        }

        .products-report-form-modal .modal-body .mb-3 {
            margin-bottom: .55rem !important;
        }

        .products-report-form-modal .form-label {
            margin-bottom: .2rem;
            font-size: .78rem;
        }

        .products-report-form-modal .form-control,
        .products-report-form-modal .form-select {
            min-height: 36px;
            padding: .3rem .5rem;
            font-size: .82rem;
        }

        .products-report-form-modal .modal-footer .btn {
            padding: .3rem .5rem;
            font-size: .74rem;
        }
    }
</style>

<div class="container-fluid products-report-page">
    <!-- Page Header -->
    <div class="page-header mb-4 products-report-heading">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="page-title">
                    <i class="bi bi-box-seam"></i> Products Report
                </h1>
                <p class="text-muted small">View and manage all products in your inventory</p>
            </div>
            <div class="col-md-4 text-end products-report-actions">
                <a href="{{ route('admin.reports.products.history') }}" class="btn btn-outline-primary me-2">
                    <i class="bi bi-clock-history"></i> Product History
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProductModal">
                    <i class="bi bi-plus-lg"></i> Add New Product
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4 products-report-summary">
        <div class="col-6 col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">Total Products</h6>
                    <h3 class="text-primary mb-0">{{ $totals['total_products'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">Active</h6>
                    <h3 class="text-success mb-0">{{ $totals['active_products'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">Avg. Margin</h6>
                    <h3 class="text-info mb-0 products-report-amount">Rs. {{ number_format($totals['total_margin'], 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">Avg. Sale Price</h6>
                    <h3 class="text-success mb-0 products-report-amount">Rs. {{ number_format($totals['avg_sale_price'], 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4 products-report-filters">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-funnel"></i> Filters
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.products.index') }}" class="row g-3">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
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

    <div class="d-flex justify-content-end align-items-center mb-2 products-report-per-page">
        <form action="{{ route('admin.reports.products.index') }}" method="GET" class="d-flex align-items-center gap-2">
            @foreach(request()->except(['page', 'per_page']) as $key => $value)
                @if(is_scalar($value))
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <label for="products-report-per-page" class="small text-muted mb-0">Per Page</label>
            <select id="products-report-per-page" name="per_page" class="form-select form-select-sm" style="width: 82px;" onchange="this.form.submit()">
                @foreach([10, 25, 50, 100] as $option)
                    <option value="{{ $option }}" @selected($perPage == $option)>{{ $option }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Products Table -->
    <div class="card products-report-table">
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
    <div class="d-flex justify-content-end mt-4 products-report-pagination">
        <div class="text-end">
            {{ $products->links() }}
        </div>
    </div>
</div>

<div class="modal fade products-report-form-modal" id="createProductModal" tabindex="-1" aria-labelledby="createProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered products-report-form-modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createProductModalLabel">Create New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.products.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_product_create_form" value="1">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="create-product-name" class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" id="create-product-name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" maxlength="255" required>
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="create-product-unit" class="form-label">Unit <span class="text-danger">*</span></label>
                        <select id="create-product-unit" name="unit" class="form-select @error('unit') is-invalid @enderror" required>
                            <option value="">-- Select Unit --</option>
                            @foreach($units as $value => $label)
                                <option value="{{ $value }}" @selected(old('unit') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('unit')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="create-product-purchase-price" class="form-label">Purchase Price (Rs.) <span class="text-danger">*</span></label>
                        <input type="number" id="create-product-purchase-price" name="purchase_price" value="{{ old('purchase_price') }}" class="form-control @error('purchase_price') is-invalid @enderror" min="0" step="0.01" required>
                        @error('purchase_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="create-product-sale-price" class="form-label">Sale Price (Rs.) <span class="text-danger">*</span></label>
                        <input type="number" id="create-product-sale-price" name="sale_price" value="{{ old('sale_price') }}" class="form-control @error('sale_price') is-invalid @enderror" min="0" step="0.01" required>
                        @error('sale_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label for="create-product-minimum-stock-level" class="form-label">Minimum Stock Level</label>
                        <input type="number" id="create-product-minimum-stock-level" name="minimum_stock_level" value="{{ old('minimum_stock_level') }}" class="form-control @error('minimum_stock_level') is-invalid @enderror" min="0" step="1" placeholder="10">
                        <small class="text-muted d-block mt-1">Alert will show when stock falls below this level</small>
                        @error('minimum_stock_level')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Create Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade products-report-form-modal" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered products-report-form-modal-dialog">
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
    @if(($errors->any() && old('_product_create_form')) || session('open_create_product_modal'))
        bootstrap.Modal.getOrCreateInstance(document.getElementById('createProductModal')).show();
    @endif

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
