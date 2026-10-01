@extends('layouts.admin')

@section('title', 'Product History - ' . $product->name)

@section('content')
<style>
    .product-history-detail-summary .card,
    .product-history-detail-summary .card *,
    .product-history-detail-summary .card::before,
    .product-history-detail-summary .card::after,
    .product-history-detail-summary .card *::before,
    .product-history-detail-summary .card *::after {
        transition: none !important;
        animation: none !important;
        transform: none !important;
        will-change: auto !important;
    }

    .product-history-detail-summary .card:hover {
        transform: none !important;
    }

    @media (min-width: 768px) and (max-width: 1199.98px) {
        .product-history-detail-summary h6 {
            font-size: .9rem;
        }

        .product-history-detail-summary h3 {
            font-size: 1.25rem;
        }

        .product-history-product-info .col-md-4 {
            display: flex;
            align-items: baseline;
            gap: .45rem;
            min-width: 0;
        }

        .product-history-product-info .col-md-4 > small {
            display: inline-block !important;
            flex: 0 0 auto;
        }

        .product-history-product-info .col-md-4 > strong,
        .product-history-product-info .col-md-4 > span {
            min-width: 0;
            overflow-wrap: anywhere;
        }
    }

    @media (max-width: 767.98px) {
        .product-history-detail-page {
            padding-top: .75rem;
        }

        .product-history-detail-header {
            flex-wrap: wrap;
            align-items: flex-start !important;
            gap: .45rem;
            margin-bottom: .85rem !important;
        }

        .product-history-detail-header > div {
            flex: 1 1 100%;
            min-width: 0;
        }

        .product-history-detail-header .page-title {
            margin-bottom: .2rem;
            font-size: 1.35rem;
            line-height: 1.2;
        }

        .product-history-detail-header .btn {
            align-self: flex-end;
            margin-left: auto;
            padding: .34rem .5rem;
            font-size: .76rem;
            line-height: 1.25;
            white-space: nowrap;
        }

        .product-history-product-info {
            margin-bottom: .8rem !important;
        }

        .product-history-product-info .card-body {
            padding: .65rem;
        }

        .product-history-product-info .row {
            --bs-gutter-x: .6rem;
            --bs-gutter-y: .6rem;
        }

        .product-history-product-info strong,
        .product-history-product-info span {
            font-size: .82rem;
            overflow-wrap: anywhere;
        }

        .product-history-sku-field {
            display: flex;
            align-items: baseline;
            gap: .45rem;
            min-width: 0;
        }

        .product-history-sku-field small {
            flex: 0 0 auto;
        }

        .product-history-sku-field span {
            min-width: 0;
        }

        .product-history-detail-summary {
            --bs-gutter-x: .65rem;
            --bs-gutter-y: .45rem;
            margin-bottom: .75rem !important;
        }

        .product-history-detail-summary > div {
            margin-bottom: 0;
            display: flex;
        }

        .product-history-detail-summary .card-body {
            padding: .55rem .4rem;
        }

        .product-history-detail-summary .card {
            width: 100%;
            height: 100%;
            margin-bottom: 0;
        }

        .product-history-detail-summary h6 {
            margin-bottom: .2rem;
            font-size: .72rem;
            line-height: 1.2;
        }

        .product-history-detail-summary h3 {
            margin-bottom: 0;
            font-size: clamp(.78rem, 3.3vw, 1rem);
            overflow-wrap: anywhere;
        }

        .product-history-detail-summary > div:nth-child(1) h3,
        .product-history-detail-summary > div:nth-child(2) h3 {
            white-space: nowrap;
        }

        .product-history-detail-table table {
            min-width: 900px;
        }
    }
</style>

<div class="container-fluid product-history-detail-page">
    <div class="page-header mb-4 d-flex justify-content-between align-items-center product-history-detail-header"><div><h1 class="page-title"><i class="bi bi-clock-history"></i> Product History</h1></div><a href="{{ route('admin.reports.products.history', array_filter($filters, fn ($value) => $value !== null && $value !== '')) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to History</a></div>

    <div class="card mb-4 product-history-product-info"><div class="card-body"><div class="row g-3"><div class="col-6 col-md-4 order-1"><small class="text-muted d-block">Product Name</small><strong>{{ $product->name }}</strong></div><div class="col-12 col-md-4 order-3 order-md-2 product-history-sku-field"><small class="text-muted">SKU</small><span>{{ $product->sku ?: '-' }}</span></div><div class="col-6 col-md-4 order-2 order-md-3"><small class="text-muted d-block">Unit</small><span>{{ $product->unit }}</span></div></div></div></div>

    <div class="row mb-4 product-history-detail-summary"><div class="col-6 col-md-4"><div class="card text-center"><div class="card-body"><h6 class="text-muted">Quantity Purchased</h6><h3 class="text-success mb-0">{{ number_format($summary->quantity_purchased, 2) }} {{ $product->unit }}</h3></div></div></div><div class="col-6 col-md-4"><div class="card text-center"><div class="card-body"><h6 class="text-muted">Quantity Sold</h6><h3 class="text-info mb-0">{{ number_format($summary->quantity_sold, 2) }} {{ $product->unit }}</h3></div></div></div><div class="col-12 col-md-4"><div class="card text-center"><div class="card-body"><h6 class="text-muted">Net Balance</h6><h3 class="text-primary mb-0">{{ number_format($summary->quantity_purchased - $summary->quantity_sold, 2) }} {{ $product->unit }}</h3></div></div></div></div>

    <div class="card product-history-detail-table"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>Date</th><th>Transaction Type</th><th>Reference / Invoice No</th><th>Quantity In</th><th>Quantity Out</th><th>Return Quantity</th><th>Running Balance</th></tr></thead><tbody>
        @forelse($transactions as $transaction)
            <tr><td>{{ optional($transaction['date'])->format('d M Y') }}</td><td>{{ $transaction['type'] }}</td><td>{{ $transaction['reference'] }}</td><td class="text-success">{{ $transaction['in'] ? number_format($transaction['in'], 2) . ' ' . $product->unit : '-' }}</td><td class="text-danger">{{ $transaction['out'] ? number_format($transaction['out'], 2) . ' ' . $product->unit : '-' }}</td><td class="text-warning">{{ $transaction['return'] ? number_format($transaction['return'], 2) . ' ' . $product->unit : '-' }}</td><td class="fw-semibold">{{ number_format($transaction['balance'], 2) }} {{ $product->unit }}</td></tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No transactions found.</td></tr>
        @endforelse
    </tbody></table></div></div>
</div>
@endsection
