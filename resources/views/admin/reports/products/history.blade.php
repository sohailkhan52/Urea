@extends('layouts.admin')

@section('title', 'Product History')

@section('content')
<style>
    .product-history-summary .card,
    .product-history-summary .card *,
    .product-history-summary .card::before,
    .product-history-summary .card::after,
    .product-history-summary .card *::before,
    .product-history-summary .card *::after {
        transition: none !important;
        animation: none !important;
        transform: none !important;
        will-change: auto !important;
    }

    .product-history-summary .card:hover {
        transform: none !important;
    }

    @media (min-width: 768px) and (max-width: 1199.98px) {
        .product-history-summary {
            --bs-gutter-x: 1rem;
            --bs-gutter-y: .75rem;
            margin-bottom: 1.25rem !important;
        }

        .product-history-summary > div {
            display: flex;
        }

        .product-history-summary .card {
            display: flex;
            width: 100%;
            min-height: 112px;
            margin-bottom: 0;
        }

        .product-history-summary .card-body {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: .85rem .6rem;
        }

        .product-history-summary h6 {
            display: flex;
            min-height: 2.4em;
            align-items: center;
            justify-content: center;
            margin-bottom: .3rem;
            font-size: .95rem;
            line-height: 1.2;
        }

        .product-history-summary h3 {
            margin-bottom: 0;
            font-size: 1.45rem;
        }

        .product-history-filters .card-body {
            padding: 1rem;
        }

        .product-history-filters form {
            --bs-gutter-x: .9rem;
            --bs-gutter-y: .8rem;
        }

        .product-history-filter-search,
        .product-history-filter-product {
            flex: 0 0 50%;
            width: 50%;
            max-width: 50%;
        }

        .product-history-filter-unit,
        .product-history-date-filter {
            flex: 0 0 33.333333%;
            width: 33.333333%;
            max-width: 33.333333%;
        }

        .product-history-filter-actions {
            display: flex;
            gap: .5rem;
        }

        .product-history-table table {
            min-width: 820px;
        }
    }

    @media (max-width: 575.98px) {
        .product-history-page {
            padding-top: .75rem;
        }

        .product-history-header {
            flex-wrap: wrap;
            align-items: flex-start !important;
            gap: .45rem;
            margin-bottom: .85rem !important;
        }

        .product-history-header > div {
            flex: 1 1 100%;
            min-width: 0;
        }

        .product-history-header .page-title {
            margin-bottom: 0;
            font-size: 1.35rem;
            line-height: 1.2;
        }

        .product-history-header p {
            display: none;
        }

        .product-history-header .btn {
            align-self: flex-end;
            margin-left: auto;
            padding: .34rem .5rem;
            font-size: .76rem;
            line-height: 1.25;
            white-space: nowrap;
        }

        .product-history-summary {
            --bs-gutter-x: .5rem;
            margin-bottom: .75rem !important;
        }

        .product-history-summary > div {
            margin-bottom: .45rem;
        }

        .product-history-summary .card-body {
            padding: .55rem .5rem;
        }

        .product-history-summary h6 {
            margin-bottom: .2rem;
            font-size: .76rem;
        }

        .product-history-summary h3 {
            margin-bottom: 0;
            font-size: clamp(1.05rem, 4.2vw, 1.3rem);
            overflow-wrap: anywhere;
        }

        .product-history-summary > div:nth-child(2) .card,
        .product-history-summary > div:nth-child(3) .card {
            height: 100%;
        }

        .product-history-summary > div:nth-child(2) .card-body,
        .product-history-summary > div:nth-child(3) .card-body {
            padding: .4rem .3rem;
        }

        .product-history-summary > div:nth-child(2) h6,
        .product-history-summary > div:nth-child(3) h6 {
            display: flex;
            min-height: 2em;
            align-items: center;
            justify-content: center;
            font-size: .64rem;
            line-height: 1.15;
        }

        .product-history-summary > div:nth-child(2) h3,
        .product-history-summary > div:nth-child(3) h3 {
            font-size: clamp(.72rem, 3.2vw, .98rem);
            white-space: nowrap;
        }

        .product-history-filters {
            margin-bottom: .8rem !important;
        }

        .product-history-filters .card-header,
        .product-history-filters .card-body {
            padding: .5rem .6rem;
        }

        .product-history-filters .card-header h5 {
            font-size: .95rem;
        }

        .product-history-filters form {
            --bs-gutter-x: .5rem;
            --bs-gutter-y: .5rem;
        }

        .product-history-filters .form-label {
            margin-bottom: .2rem;
            font-size: .76rem !important;
        }

        .product-history-filters .form-control,
        .product-history-filters .form-select {
            min-height: 35px;
            padding: .28rem .45rem;
            font-size: .8rem;
        }

        .product-history-filter-actions {
            display: flex;
            gap: .45rem;
        }

        .product-history-filter-actions .btn {
            display: inline-flex;
            flex: 1 1 0;
            align-items: center;
            justify-content: center;
            gap: .25rem;
            min-height: 35px;
            padding: .3rem .45rem;
            font-size: .76rem;
            text-align: center;
        }

        .product-history-table table {
            min-width: 680px;
        }
    }

    @media (max-width: 380px) {
        .product-history-date-filter {
            flex: 0 0 100%;
            max-width: 100%;
        }
    }
</style>

<div class="container-fluid product-history-page">
    <div class="page-header mb-4 d-flex justify-content-between align-items-center product-history-header">
        <div>
            <h1 class="page-title"><i class="bi bi-clock-history"></i> Product History</h1>
            <p class="text-muted small mb-0">View purchase and sales quantity history for your products</p>
        </div>
        <a href="{{ route('admin.reports.products.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Products</a>
    </div>

    <div class="row mb-4 product-history-summary">
        <div class="col-12 col-md-4"><div class="card text-center"><div class="card-body"><h6 class="text-muted">Total Products</h6><h3 class="text-primary mb-0">{{ $summary['total_products'] }}</h3></div></div></div>
        <div class="col-6 col-md-4"><div class="card text-center"><div class="card-body"><h6 class="text-muted">Total Purchased Quantity</h6><h3 class="text-success mb-0">{{ number_format($summary['total_purchased'], 2) }}</h3></div></div></div>
        <div class="col-6 col-md-4"><div class="card text-center"><div class="card-body"><h6 class="text-muted">Total Sold Quantity</h6><h3 class="text-info mb-0">{{ number_format($summary['total_sold'], 2) }}</h3></div></div></div>
    </div>

    <div class="card mb-4 product-history-filters">
        <div class="card-header"><h5 class="mb-0"><i class="bi bi-funnel"></i> Filters</h5></div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.products.history') }}" class="row g-3">
                <div class="col-12 col-md-3 product-history-filter-search"><label class="form-label small">Search Product Name or SKU</label><input type="text" name="search" class="form-control form-control-sm" value="{{ $filters['search'] }}" placeholder="Search..."></div>
                <div class="col-12 col-md-3 product-history-filter-product"><label class="form-label small">Product</label><select name="product_id" class="form-select form-select-sm"><option value="">All Products</option>@foreach($allProducts as $product)<option value="{{ $product->id }}" @selected($filters['product_id'] === $product->id)>{{ $product->name }}{{ $product->sku ? ' - ' . $product->sku : '' }}</option>@endforeach</select></div>
                <div class="col-12 col-md-2 product-history-filter-unit"><label class="form-label small">Unit</label><select name="unit" class="form-select form-select-sm"><option value="">All Units</option>@foreach($units as $value => $label)<option value="{{ $value }}" @selected($filters['unit'] === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="col-6 col-md-2 product-history-date-filter"><label class="form-label small">Date From</label><input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] }}"></div>
                <div class="col-6 col-md-2 product-history-date-filter"><label class="form-label small">Date To</label><input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] }}"></div>
                <div class="col-12 product-history-filter-actions"><button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Filter</button><a href="{{ route('admin.reports.products.history') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i> Clear</a></div>
            </form>
        </div>
    </div>

    <div class="card product-history-table"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>Product Name</th><th>Quantity Purchased</th><th>Quantity Sold</th><th>Unit</th><th class="text-center">Action</th></tr></thead><tbody>
        @forelse($products as $row)
            <tr><td><a href="{{ route('admin.reports.products.history.show', array_merge([$row->product], array_filter($filters, fn ($value) => $value !== null && $value !== ''))) }}" class="fw-semibold text-decoration-none">{{ $row->product->name }}</a><br><small class="text-muted">{{ $row->product->sku ?: 'No SKU' }}</small></td><td class="text-success fw-semibold">{{ number_format($row->quantity_purchased, 2) }} {{ $row->product->unit }}</td><td class="text-info fw-semibold">{{ number_format($row->quantity_sold, 2) }} {{ $row->product->unit }}</td><td>{{ $units[$row->product->unit] ?? $row->product->unit }}</td><td class="text-center"><a href="{{ route('admin.reports.products.history.show', array_merge([$row->product], array_filter($filters, fn ($value) => $value !== null && $value !== ''))) }}" class="btn btn-sm btn-outline-primary" title="View Details"><i class="bi bi-eye"></i></a></td></tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-inbox"></i> No product history found.</td></tr>
        @endforelse
    </tbody></table></div></div>
</div>
@endsection
