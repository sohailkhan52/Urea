@extends('layouts.admin')

@php
    $company = \App\Models\Company::active()->first();
    $headerPhones = array_filter([
        $company?->phone ?: '03239123800',
        $company?->additional_number,
    ]);
@endphp

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-title">{{ $supplier->name }} - Payment History</h1>
                <p class="text-muted">Transaction history and payment records</p>
            </div>
            <div class="col-auto">
                                          
                <div class="card-body">
                <a href="{{ route('admin.supplier-payables.show', $supplier->id) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Supplier
                </a>

                    <a href="javascript:window.print()" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-printer me-1"></i> Print
                    </a>
                
                    
            </div>
            </div>
        </div>
    </div>

    <div class="print-history-container d-none print-only">
        <header class="history-print-header">
            <div>
                <div class="company-name">{{ $company?->name ?? 'DeraNexa' }}</div>
                <div>{{ implode(' / ', $headerPhones) }}</div>
            </div>
            <div class="history-print-title">
                <div>Payment History</div>
                <small>Payment #: {{ $payments->first()?->payment_number ?? '-' }}</small>
                <small>Date: {{ now()->format('d M Y') }}</small>
            </div>
        </header>

        <div class="history-supplier-details">
            <div class="history-supplier-detail-row">
                <span>Supplier</span>
                <strong>{{ $supplier->name ?: '-' }}</strong>
            </div>
            <div class="history-supplier-detail-row">
                <span>Company</span>
                <strong>{{ $supplier->company_name ?: '-' }}</strong>
            </div>
            <div class="history-supplier-detail-row">
                <span>Phone</span>
                <strong>{{ $supplier->phone ?: '-' }}</strong>
            </div>
        </div>

        <table class="history-print-table">
            <thead>
                <tr>
                    <th>Payment #</th>
                    <th>Date &amp; Time</th>
                    <th>Purchase PO</th>
                    <th class="text-right">Amount</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Recorded By</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->payment_number }}</td>
                        <td>{{ $payment->payment_date->format('d M Y H:i:s') }}</td>
                        <td>{{ $payment->purchase?->purchase_number ?? '-' }}</td>
                        <td class="text-right">Rs. {{ number_format($payment->amount, 0) }}</td>
                        <td>{{ ucfirst($payment->payment_method) }}</td>
                        <td>{{ $payment->reference_number ?: '-' }}</td>
                        <td>{{ $payment->recorder?->name ?? 'Unknown' }}</td>
                        <td>{{ $payment->notes ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No payment transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="history-print-footer">
            Address: {{ $company?->address ?: 'Naivela Dera Ismail Khan' }}
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Payments</p>
                    <h4 class="mb-0">{{ $payments->total() }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card success">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Amount Paid</p>
                    <h4 class="mb-0">Rs. {{ number_format($payments->sum('amount') ?? 0, 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card info">
                <div class="card-body">
                    <p class="text-muted mb-1">This Page</p>
                    <h4 class="mb-0">{{ $payments->count() }} records</h4>
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
                                    <strong>Rs. {{ number_format($payment->amount, 0) }}</strong>
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
        @page {
            margin: 0.5in;
        }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            color: #000 !important;
        }

        body {
            background: #fff !important;
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }

        .no-print,
        .toggle-sidebar,
        .page-header,
        .btn,
        .modal,
        .pagination,
        .card,
        .card-header,
        .card-body,
        .table-responsive,
        .table,
        .row,
        .col,
        .col-md-4,
        .col-auto,
            .container-fluid > *:not(.print-history-container) {
            display: none !important;
            visibility: hidden !important;
        }

        .container-fluid {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .print-history-container {
            display: block !important;
            width: 100%;
            background: #fff;
            color: #000;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            padding: 0;
            margin: 0;
        }

        .history-print-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 12px;
            border-bottom: 3px solid #000;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }

        .history-print-title {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            text-align: right;
        }

        .history-print-title > div,
        .history-print-header .company-name {
            font-size: 24px;
            font-weight: 800;
            line-height: 1.2;
        }

        .history-supplier-details {
            width: 62%;
            margin: 0 auto 18px;
            font-size: 11px;
            line-height: 1.5;
        }

        .history-supplier-detail-row {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 18px;
            min-height: 16px;
        }

        .history-supplier-detail-row strong {
            font-weight: 700;
        }

        .history-print-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .history-print-table th,
        .history-print-table td {
            padding: 7px 6px;
            border-bottom: 1px solid #000;
            vertical-align: top;
            color: #000 !important;
        }

        .history-print-table thead th {
            border-bottom: 2px solid #000;
            font-weight: 700;
        }

        .history-print-footer {
            position: fixed;
            right: 0;
            bottom: 0;
            left: 0;
            padding-top: 6px;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 11px;
            color: #000 !important;
        }

        .statement-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0 0 12px;
            border-bottom: 3px solid #000;
            margin-bottom: 20px;
        }

        .company-block {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .company-name {
            font-size: 32px;
            font-weight: 800;
            line-height: 1.1;
            color: #000 !important;
            margin-bottom: 2px;
        }

        .company-person,
        .company-phone {
            font-size: 17px;
            color: #000 !important;
            line-height: 1.4;
        }

        .statement-right {
            text-align: right;
            margin-top: 4px;
        }

        .statement-title {
            font-size: 32px;
            font-weight: 800;
            line-height: 1.2;
            color: #000 !important;
            margin-bottom: 6px;
        }

        .statement-meta,
        .statement-date {
            font-size: 18px;
            color: #000 !important;
            line-height: 1.4;
            font-weight: 500;
        }

        .supplier-info-box {
            width: 100%;
            margin: 18px 0 20px;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .supplier-info-row {
            display: flex;
            align-items: center;
            min-height: 26px;
            font-size: 13px;
            color: #000;
            width: 100%;
            margin-bottom: 4px;
            gap: 12px;
        }

        .supplier-info-split {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding-right: 0;
        }

        .supplier-info-left,
        .supplier-info-right-side {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .supplier-info-left {
            margin-left: 0;
            padding-left: 0;
            flex: 1;
        }

        .supplier-info-right-side {
            margin-left: 36px;
            justify-content: flex-start;
            text-align: left;
            padding-right: 0;
            min-width: 0;
            width: 42%;
            align-items: flex-start;
            flex-direction: column;
            gap: 0;
        }

        .compact-right-block {
            display: flex;
            flex-direction: column;
            gap: 0;
            width: 42%;
        }

        .right-inline-pair {
            display: flex;
            align-items: baseline;
            gap: 6px;
            white-space: normal;
            line-height: 1.3;
        }

        .supplier-info-right-side .supplier-info-right-label,
        .supplier-info-right-side .supplier-info-right-value {
            text-align: left;
        }

        .supplier-info-label,
        .supplier-info-right-label {
            font-weight: 700;
            display: inline-block;
        }

        .supplier-info-value,
        .supplier-info-right-value {
            font-weight: 500;
        }

        .statement-section {
            margin-top: 20px;
        }

        .section-heading {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 22px;
            font-weight: 800;
            color: #000;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #000;
        }

        .section-icon {
            font-size: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .statement-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
            table-layout: fixed;
        }

        .statement-table th,
        .statement-table td {
            text-align: left;
            padding: 8px 10px;
            border-bottom: 1px solid #000;
            vertical-align: top;
            color: #000 !important;
        }

        .statement-table thead th {
            border-bottom: 2px solid #000;
            font-weight: 700;
            background: transparent;
        }

        .statement-total-row td {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-weight: 700;
            background: transparent;
        }

        .text-right {
            text-align: right !important;
        }

        .payment-table th,
        .payment-table td {
            font-size: 14px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            padding-top: 10px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            align-items: end;
        }

        .summary-left,
        .summary-right {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 18px;
            color: #000;
            padding: 2px 0;
            border-bottom: 1px solid #000;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-right {
            margin-top: 46px;
        }

        .summary-right .summary-row {
            justify-content: space-between;
            gap: 20px;
            font-weight: 700;
            border-bottom: none;
            width: 100%;
            padding: 0;
            margin: 0;
            text-align: right;

        }

        .payable-amount {
            font-size: 30px;
            font-weight: 800;
            display: inline-block;
            min-width: 50px;
        }
    }
</style>
@endsection
