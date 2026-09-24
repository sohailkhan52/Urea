@extends('layouts.admin')

@section('title', 'Product History - ' . $product->name)

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4 d-flex justify-content-between align-items-center"><div><h1 class="page-title"><i class="bi bi-clock-history"></i> Product History</h1><p class="text-muted small mb-0">{{ $product->name }}{{ $product->sku ? ' - ' . $product->sku : '' }}</p></div><a href="{{ route('admin.reports.products.history', array_filter($filters, fn ($value) => $value !== null && $value !== '')) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to History</a></div>

    <div class="card mb-4"><div class="card-body"><div class="row g-3"><div class="col-md-4"><small class="text-muted d-block">Product Name</small><strong>{{ $product->name }}</strong></div><div class="col-md-4"><small class="text-muted d-block">SKU</small><span>{{ $product->sku ?: '-' }}</span></div><div class="col-md-4"><small class="text-muted d-block">Unit</small><span>{{ $product->unit }}</span></div></div></div></div>

    <div class="row mb-4"><div class="col-md-4"><div class="card text-center"><div class="card-body"><h6 class="text-muted">Quantity Purchased</h6><h3 class="text-success mb-0">{{ number_format($summary->quantity_purchased, 2) }} {{ $product->unit }}</h3></div></div></div><div class="col-md-4"><div class="card text-center"><div class="card-body"><h6 class="text-muted">Quantity Sold</h6><h3 class="text-info mb-0">{{ number_format($summary->quantity_sold, 2) }} {{ $product->unit }}</h3></div></div></div><div class="col-md-4"><div class="card text-center"><div class="card-body"><h6 class="text-muted">Net Balance</h6><h3 class="text-primary mb-0">{{ number_format($summary->quantity_purchased - $summary->quantity_sold, 2) }} {{ $product->unit }}</h3></div></div></div></div>

    <div class="card"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>Date</th><th>Transaction Type</th><th>Reference / Invoice No</th><th>Quantity In</th><th>Quantity Out</th><th>Return Quantity</th><th>Running Balance</th></tr></thead><tbody>
        @forelse($transactions as $transaction)
            <tr><td>{{ optional($transaction['date'])->format('d M Y') }}</td><td>{{ $transaction['type'] }}</td><td>{{ $transaction['reference'] }}</td><td class="text-success">{{ $transaction['in'] ? number_format($transaction['in'], 2) . ' ' . $product->unit : '-' }}</td><td class="text-danger">{{ $transaction['out'] ? number_format($transaction['out'], 2) . ' ' . $product->unit : '-' }}</td><td class="text-warning">{{ $transaction['return'] ? number_format($transaction['return'], 2) . ' ' . $product->unit : '-' }}</td><td class="fw-semibold">{{ number_format($transaction['balance'], 2) }} {{ $product->unit }}</td></tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No transactions found.</td></tr>
        @endforelse
    </tbody></table></div></div>
</div>
@endsection
