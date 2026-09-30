@extends('layouts.admin')

@section('title', 'Account Statement - ' . $customer->name)

@section('content')
<div class="container-fluid customer-statement-page">
    <div class="statement-print-header">
        <div>
            <div class="company-name">{{ $company?->name ?? 'DeraNexa' }}</div>
            <div>{{ implode(' / ', array_filter([$company?->phone ?: '03239123800', $company?->additional_number])) }}</div>
        </div>
        <div class="print-title-block">
            <div class="print-title">Customer Account Statement</div>
            <small>Customer: {{ $customer->name }}</small>
            <small>Generated: {{ now()->format('d M Y H:i') }}</small>
        </div>
    </div>

    <div class="mb-4 no-print statement-page-heading">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0 statement-page-title">Customer Account Statement</h1>
            </div>
            <div class="btn-group statement-page-actions">
                <button type="button" class="btn btn-info" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </div>
        </div>
    </div>

    {{-- Date Range Filter --}}
    <div class="card mb-4 no-print statement-date-filter">
        <div class="card-body">
            <form action="{{ route('admin.customers.statement', $customer) }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" 
                               class="form-control" 
                               id="start_date" 
                               name="start_date" 
                               value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" 
                               class="form-control" 
                               id="end_date" 
                               name="end_date" 
                               value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2 statement-filter-action">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2 statement-filter-action">
                        <a href="{{ route('admin.customers.statement', $customer) }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Statement Content --}}
    <div class="card">
        <div class="card-body">
            {{-- Statement Header --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <h4 class="mb-3">Customer Information</h4>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="150"><strong>Name:</strong></td>
                            <td>{{ $customer->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Mobile:</strong></td>
                            <td>{{ $customer->phone ?? 'N/A' }}</td>
                        </tr>
                        @if($customer->family)
                        <tr>
                            <td><strong>Family:</strong></td>
                            <td>{{ $customer->family->name }} ({{ $customer->family->family_code }})</td>
                        </tr>
                        @endif
                        <tr>
                            <td><strong>Warehouse:</strong></td>
                            <td>{{ $customer->warehouse->name ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6 text-end statement-period-block">
                    <h4 class="mb-3">Statement Period</h4>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="150"><strong>From:</strong></td>
                            <td>{{ request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->format('M d, Y') : 'Beginning' }}</td>
                        </tr>
                        <tr>
                            <td><strong>To:</strong></td>
                            <td>{{ request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->format('M d, Y') : 'Today' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Generated:</strong></td>
                            <td>{{ now()->format('M d, Y h:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Opening Balance --}}
            @if($openingBalance != 0)
            <div class="alert alert-info">
                <strong>Opening Balance:</strong> Rs. {{ number_format(abs($openingBalance), 2) }}
                @if($openingBalance > 0)
                    <span class="text-danger">(Debit)</span>
                @else
                    <span class="text-success">(Credit)</span>
                @endif
            </div>
            @endif

            {{-- Transaction Table --}}
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th width="100">Date</th>
                            <th width="150">Reference</th>
                            <th>Description</th>
                            <th width="120" class="text-end">Sale</th>
                            <th width="120" class="text-end">Paid</th>
                            <th width="120" class="text-end">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($statement['transactions']) > 0)
                            @foreach($statement['transactions'] as $transaction)
                            <tr>
                                <td>
                                    <small>{{ $transaction['date']->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    @if($transaction['type'] === 'sale')
                                        {{ $transaction['reference'] }}
                                    @else
                                        {{ $transaction['reference'] }}
                                    @endif
                                </td>
                                <td>{{ $transaction['description'] }}</td>
                                <td class="text-end">
                                    @if($transaction['debit'] > 0)
                                        {{ number_format($transaction['debit'], 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($transaction['credit'] > 0)
                                        {{ number_format($transaction['credit'], 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end">
                                    <strong>{{ number_format($transaction['balance'], 2) }}</strong>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center text-muted">No transactions in this period</td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">Totals:</th>
                            <th class="text-end">{{ number_format($statement['summary']['total_sales'], 2) }}</th>
                            <th class="text-end">{{ number_format($statement['summary']['total_payments'], 2) }}</th>
                            <th class="text-end">
                                <strong class="{{ $statement['summary']['current_balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($statement['summary']['current_balance'], 2) }}
                                </strong>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>

    <div class="statement-print-footer">
        Address: {{ $company?->address ?: 'Naivela Dera Ismail khan' }}
    </div>
</div>

@endsection

@push('styles')
<style>
    .statement-print-header,
    .statement-print-footer {
        display: none;
    }

@media screen and (max-width: 576px) {
    .customer-statement-page .statement-page-heading > .d-flex {
        align-items: center !important;
        flex-direction: row;
        justify-content: space-between;
        gap: 6px;
    }

    .customer-statement-page .statement-page-heading > .d-flex > div:first-child {
        flex: 1 1 auto;
        min-width: 0;
    }

    .customer-statement-page .statement-page-title {
        font-size: clamp(0.82rem, 3.7vw, 1rem);
        line-height: 1.25;
        white-space: nowrap;
    }

    .customer-statement-page .statement-page-heading p {
        font-size: 0.8rem;
    }

    .customer-statement-page .statement-page-actions {
        display: inline-flex;
        flex: 0 0 auto;
        align-self: center;
    }

    .customer-statement-page .statement-page-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 30px;
        padding: 3px 7px;
        font-size: 0.72rem;
        line-height: 1.2;
        text-align: center;
        white-space: nowrap;
    }

    .customer-statement-page .statement-date-filter .card-body {
        padding: 12px;
    }

    .customer-statement-page .statement-date-filter .row {
        --bs-gutter-x: 0.65rem;
        --bs-gutter-y: 0.55rem;
    }

    .customer-statement-page .statement-date-filter .form-label {
        margin-bottom: 3px;
        font-size: 0.8rem;
    }

    .customer-statement-page .statement-date-filter .form-control {
        min-height: 36px;
        padding: 5px 8px;
        font-size: 0.875rem;
    }

    .customer-statement-page .statement-date-filter .btn {
        min-height: 34px;
        padding: 5px 8px;
        font-size: 0.8rem;
    }

    .customer-statement-page .statement-date-filter .statement-filter-action {
        flex: 0 0 50%;
        width: 50%;
    }

    .customer-statement-page .statement-date-filter .statement-filter-action .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 32px;
        padding: 4px 6px;
        font-size: 0.76rem;
        text-align: center;
        white-space: nowrap;
    }

    .customer-statement-page .statement-period-block {
        text-align: left !important;
    }

    .customer-statement-page .statement-period-block h4 {
        text-align: center;
    }

    .customer-statement-page .statement-period-block table {
        width: 100%;
        table-layout: fixed;
    }

    .customer-statement-page .statement-period-block table td:first-child {
        width: 42% !important;
        text-align: left;
    }

    .customer-statement-page .statement-period-block table td:last-child {
        text-align: right;
        overflow-wrap: anywhere;
    }
}

@media print {
    @page {
        size: A4 portrait;
        margin: 8mm;
    }

    html,
    body {
        height: auto !important;
        min-height: 0 !important;
        font-size: 12px !important;
        color: #000 !important;
    }

    .sidebar,
    .topbar,
    .no-print {
        display: none !important;
    }

    .content {
        margin: 0 !important;
        padding: 0 !important;
    }

    .customer-statement-page {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .statement-print-header {
        display: flex !important;
        align-items: flex-start;
        justify-content: space-between;
        padding-bottom: 12px;
        border-bottom: 2px solid #000;
        margin-bottom: 20px;
        font-size: 11px;
        line-height: 1.4;
    }

    .statement-print-header .company-name,
    .statement-print-header .print-title {
        font-size: 24px;
        font-weight: 800;
        line-height: 1.2;
    }

    .statement-print-header .print-title-block {
        text-align: right;
    }

    .statement-print-header .print-title-block small {
        display: block;
        font-size: 11px;
        font-weight: 400;
    }

    .statement-print-footer {
        display: block !important;
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

    .customer-statement-page .card {
        border: none !important;
        box-shadow: none !important;
        break-inside: avoid;
    }

    .customer-statement-page .card-body {
        padding: 0 !important;
    }

    .customer-statement-page table {
        color: #000 !important;
    }

    .customer-statement-page .statement-period-block {
        display: none !important;
    }

    .statement-print-header h1,
    .statement-print-header h2,
    .statement-print-header p {
        margin: 0;
    }
}
</style>
@endpush
