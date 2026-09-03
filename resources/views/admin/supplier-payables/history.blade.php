@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-title">{{ $supplier->name }} - Payment History</h1>
                <p class="text-muted">Transaction history and payment records</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.supplier-payables.show', $supplier->id) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Supplier
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Payments</p>
                    <h4 class="mb-0">{{ $payments->total() }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card success">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Amount Paid</p>
                    <h4 class="mb-0">Rs. {{ number_format($payments->sum('amount') ?? 0, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card info">
                <div class="card-body">
                    <p class="text-muted mb-1">This Page</p>
                    <h4 class="mb-0">{{ $payments->count() }} records</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <a href="javascript:window.print()" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-printer me-1"></i> Print
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Payment Transactions</h5>
        </div>
        <div class="card-body">
            @if($payments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Payment #</th>
                                <th>Date & Time</th>
                                <th>Purchase PO</th>
                                <th class="text-end">Amount</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th>Recorded By</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                            <tr>
                                <td>
                                    <strong>{{ $payment->payment_number }}</strong>
                                </td>
                                <td>
                                    <small>
                                        {{ $payment->payment_date->format('d M Y') }}<br>
                                        {{ $payment->payment_date->format('H:i:s') }}
                                    </small>
                                </td>
                                <td>
                                    @if($payment->purchase)
                                        <a href="{{ route('admin.purchases.show', $payment->purchase->id) }}" target="_blank" class="text-decoration-none">
                                            {{ $payment->purchase->purchase_number }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <strong>Rs. {{ number_format($payment->amount, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($payment->payment_method) }}</span>
                                </td>
                                <td>
                                    @if($payment->reference_number)
                                        <small>{{ $payment->reference_number }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small>
                                        @if($payment->recorder)
                                            {{ $payment->recorder->name }}
                                        @else
                                            <span class="text-muted">Unknown</span>
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    @if($payment->notes)
                                        <small>{{ Str::limit($payment->notes, 50) }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $payments->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">No payment transactions found</p>
                    <small>This supplier hasn't received any payments yet.</small>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Print Styles -->
<style media="print">
    @media print {
        body {
            background: white;
            color: #000;
        }
        .page-header,
        .btn,
        .modal,
        .pagination,
        .card-header {
            display: none;
        }
        .table {
            font-size: 10pt;
        }
        .container-fluid {
            max-width: 100%;
        }
        .card {
            page-break-inside: avoid;
        }
    }
</style>
@endsection
