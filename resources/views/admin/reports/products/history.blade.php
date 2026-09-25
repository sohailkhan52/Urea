@extends('layouts.admin')

@section('title', 'Product History')

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title"><i class="bi bi-clock-history"></i> Product History</h1>
            <p class="text-muted small mb-0">View purchase and sales quantity history for your products</p>
        </div>
        <a href="{{ route('admin.reports.products.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Products</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4"><div class="card text-center"><div class="card-body"><h6 class="text-muted">Total Products</h6><h3 class="text-primary mb-0">{{ $summary['total_products'] }}</h3></div></div></div>
        <div class="col-md-4"><div class="card text-center"><div class="card-body"><h6 class="text-muted">Total Purchased Quantity</h6><h3 class="text-success mb-0">{{ number_format($summary['total_purchased'], 2) }}</h3></div></div></div>
        <div class="col-md-4"><div class="card text-center"><div class="card-body"><h6 class="text-muted">Total Sold Quantity</h6><h3 class="text-info mb-0">{{ number_format($summary['total_sold'], 2) }}</h3></div></div></div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0"><i class="bi bi-funnel"></i> Filters</h5></div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.products.history') }}" class="row g-3">
                <div class="col-md-3"><label class="form-label small">Search Product Name or SKU</label><input type="text" name="search" class="form-control form-control-sm" value="{{ $filters['search'] }}" placeholder="Search..."></div>
                <div class="col-md-3"><label class="form-label small">Product</label><select name="product_id" class="form-select form-select-sm"><option value="">All Products</option>@foreach($allProducts as $product)<option value="{{ $product->id }}" @selected($filters['product_id'] === $product->id)>{{ $product->name }}{{ $product->sku ? ' - ' . $product->sku : '' }}</option>@endforeach</select></div>
                <div class="col-md-2"><label class="form-label small">Unit</label><select name="unit" class="form-select form-select-sm"><option value="">All Units</option>@foreach($units as $value => $label)<option value="{{ $value }}" @selected($filters['unit'] === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="col-md-2"><label class="form-label small">Date From</label><input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] }}"></div>
                <div class="col-md-2"><label class="form-label small">Date To</label><input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] }}"></div>
                <div class="col-12"><button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Filter</button><a href="{{ route('admin.reports.products.history') }}" class="btn btn-outline-secondary btn-sm ms-2"><i class="bi bi-x-circle"></i> Clear</a></div>
            </form>
        </div>
    </div>

    <div class="card"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>Product Name</th><th>Quantity Purchased</th><th>Quantity Sold</th><th>Unit</th><th class="text-center">Action</th></tr></thead><tbody>
        @forelse($products as $row)
            <tr><td><a href="{{ route('admin.reports.products.history.show', array_merge([$row->product], array_filter($filters, fn ($value) => $value !== null && $value !== ''))) }}" class="fw-semibold text-decoration-none">{{ $row->product->name }}</a><br><small class="text-muted">{{ $row->product->sku ?: 'No SKU' }}</small></td><td class="text-success fw-semibold">{{ number_format($row->quantity_purchased, 2) }} {{ $row->product->unit }}</td><td class="text-info fw-semibold">{{ number_format($row->quantity_sold, 2) }} {{ $row->product->unit }}</td><td>{{ $units[$row->product->unit] ?? $row->product->unit }}</td><td class="text-center"><a href="{{ route('admin.reports.products.history.show', array_merge([$row->product], array_filter($filters, fn ($value) => $value !== null && $value !== ''))) }}" class="btn btn-sm btn-outline-primary" title="View Details"><i class="bi bi-eye"></i></a></td></tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-inbox"></i> No product history found.</td></tr>
        @endforelse
    </tbody></table></div></div>
</div>
@endsection
