@extends('layouts.admin')

@section('title', 'Product History - ' . $product->name)

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title"><i class="bi bi-clock-history"></i> Product History</h1>
            <p class="text-muted small mb-0">{{ $product->name }}{{ $product->sku ? ' - ' . $product->sku : '' }}</p>
        </div>
        <a href="{{ route('admin.reports.products.history', array_filter($filters, fn ($value) => $value !== null && $value !== '')) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to History
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <small class="text-muted d-block">Product Name</small>
                    <strong>{{ $product->name }}</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">SKU</small>
                    <span>{{ $product->sku ?: '-' }}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Base Unit</small>
                    <span>{{ $product->baseUnit ? $product->baseUnit->name : $product->unit }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Quantity Purchased</h6>
                    <h3 class="text-success mb-0">
                        {{ number_format($summary->quantity_purchased, 2) }} 
                        {{ $product->baseUnit ? $product->baseUnit->abbreviation : $product->unit }}
                    </h3>
                    <small class="text-muted">(in base units)</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Quantity Sold</h6>
                    <h3 class="text-info mb-0">
                        {{ number_format($summary->quantity_sold, 2) }} 
                        {{ $product->baseUnit ? $product->baseUnit->abbreviation : $product->unit }}
                    </h3>
                    <small class="text-muted">(in base units)</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Net Balance</h6>
                    <h3 class="text-primary mb-0">
                        {{ number_format($summary->quantity_purchased - $summary->quantity_sold, 2) }} 
                        {{ $product->baseUnit ? $product->baseUnit->abbreviation : $product->unit }}
                    </h3>
                    <small class="text-muted">(in base units)</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Transaction History</h5>
            <small class="text-muted">All balances shown in base units for accurate tracking</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Transaction Type</th>
                        <th>Reference / Invoice No</th>
                        <th>Transaction Qty</th>
                        <th>Base Qty In</th>
                        <th>Base Qty Out</th>
                        <th>Base Qty Return</th>
                        <th>Running Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ optional($transaction['date'])->format('d M Y') }}</td>
                            <td>{{ $transaction['type'] }}</td>
                            <td>{{ $transaction['reference'] }}</td>
                            <td>
                                @if(isset($transaction['transaction_quantity']) && isset($transaction['unit_name']))
                                    <span class="badge bg-light text-dark">
                                        {{ number_format($transaction['transaction_quantity'], 2) }} {{ $transaction['unit_name'] }}
                                    </span>
                                    @if(isset($transaction['conversion_factor']) && $transaction['conversion_factor'] != 1)
                                        <br><small class="text-muted">({{ $transaction['conversion_factor'] }}× base)</small>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-success">
                                {{ $transaction['in'] ? number_format($transaction['in'], 2) : '-' }}
                            </td>
                            <td class="text-danger">
                                {{ $transaction['out'] ? number_format($transaction['out'], 2) : '-' }}
                            </td>
                            <td class="text-warning">
                                {{ $transaction['return'] ? number_format($transaction['return'], 2) : '-' }}
                            </td>
                            <td class="fw-semibold">
                                {{ number_format($transaction['balance'], 2) }}
                                {{ $product->baseUnit ? $product->baseUnit->abbreviation : $product->unit }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
