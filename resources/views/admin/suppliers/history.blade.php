@extends('layouts.admin')

@section('title', 'Payment History - ' . $supplier->name)

@section('content')
<style>
    .print-report-header,
    .print-report-footer,
    .print-supplier-details {
        display: none;
    }

    @media (max-width: 575.98px) {
        .supplier-history-tabs {
            overflow-x: auto;
            padding: .5rem !important;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        .supplier-history-tabs::-webkit-scrollbar {
            display: none;
        }

        .supplier-history-tabs .nav-tabs {
            display: flex;
            flex-wrap: nowrap;
            gap: .25rem;
            width: max-content;
            min-width: 100%;
            border-bottom: 0;
        }

        .supplier-history-tabs .nav-item {
            flex: 0 0 auto;
        }

        .supplier-history-tabs .nav-link {
            padding: .45rem .55rem;
            border: 0;
            border-radius: .45rem;
            color: #526174;
            font-size: .68rem;
            line-height: 1.2;
            white-space: nowrap;
        }

        .supplier-history-tabs .nav-link.active {
            color: #fff;
            background: #1769ff;
            box-shadow: 0 2px 6px rgba(23, 105, 255, .2);
        }

        .supplier-history-metrics {
            --bs-gutter-x: .75rem;
            --bs-gutter-y: .75rem;
        }

        .supplier-history-metrics > [class*="col-"] {
            display: flex;
            flex: 0 0 50%;
            max-width: 50%;
        }

        .supplier-history-metrics .card {
            width: 100%;
            min-height: 102px;
            margin-bottom: 0 !important;
            transition: none !important;
            animation: none !important;
            transform: none !important;
            will-change: auto !important;
        }

        .supplier-history-metrics .card,
        .supplier-history-metrics .card *,
        .supplier-history-metrics .card::before,
        .supplier-history-metrics .card::after,
        .supplier-history-metrics .card *::before,
        .supplier-history-metrics .card *::after {
            transition: none !important;
            animation: none !important;
        }

        .supplier-history-metrics .card:hover {
            transform: none !important;
        }

        .supplier-history-metrics .card-body {
            padding: .65rem !important;
        }

        .supplier-history-metrics .card-body small {
            font-size: .72rem;
            line-height: 1.25;
        }

        .supplier-history-metrics .card-body h4 {
            font-size: .9rem;
            overflow-wrap: anywhere;
        }

        .supplier-history-summary .card-body {
            padding: .65rem .75rem;
        }

        .supplier-history-summary .row {
            --bs-gutter-x: .5rem;
            --bs-gutter-y: .45rem;
        }

        .supplier-history-summary .row > [class*="col-"] {
            display: grid;
            grid-template-columns: 28% minmax(0, 1fr);
            column-gap: .4rem;
            align-items: center;
            min-width: 0;
        }

        .supplier-history-summary .row > [class*="col-"] > small {
            grid-column: 1;
            margin: 0;
            font-size: .72rem;
            white-space: nowrap;
        }

        .supplier-history-summary .row > [class*="col-"] > strong,
        .supplier-history-summary .row > [class*="col-"] > span {
            grid-column: 2;
            min-width: 0;
            font-size: .82rem;
            line-height: 1.2;
            white-space: nowrap;
        }

        .supplier-history-heading {
            flex-wrap: wrap;
            gap: .35rem;
            margin-bottom: .85rem !important;
        }

        .supplier-history-heading > div:first-child {
            flex: 0 0 100%;
            min-width: 0;
        }

        .supplier-history-heading h1 {
            font-size: 1rem;
            white-space: nowrap;
        }

        .supplier-history-heading p {
            display: none;
        }

        .supplier-history-actions {
            flex: 0 0 100%;
            justify-content: flex-end;
            gap: .2rem !important;
        }

        .supplier-history-actions .btn {
            padding: .35rem .5rem;
            font-size: .7rem;
            line-height: 1.2;
            white-space: nowrap;
        }

        .supplier-history-actions .btn i {
            margin-right: .1rem !important;
            font-size: .65rem;
        }
    }

    @media (min-width: 576px) and (max-width: 991.98px) {
        .supplier-history-heading {
            gap: .75rem;
        }

        .supplier-history-heading > div:first-child {
            min-width: 0;
        }

        .supplier-history-heading h1 {
            font-size: 1.25rem;
        }

        .supplier-history-heading p {
            font-size: .85rem;
        }

        .supplier-history-actions {
            flex: 0 0 auto;
            gap: .35rem !important;
        }

        .supplier-history-actions .btn {
            padding: .38rem .5rem;
            font-size: .76rem;
            white-space: nowrap;
        }
    }

    @media print {
        .sidebar,
        .sidebar-backdrop,
        .topbar {
            display: none !important;
        }

        .main-wrapper {
            width: 100% !important;
            margin-left: 0 !important;
            min-height: 0 !important;
        }

        .content {
            padding: 0 !important;
        }

        .print-report-header {
            display: flex !important;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #000;
            padding: 0 0 10px;
            margin-bottom: 18px;
            color: #000 !important;
        }

        .print-report-header strong {
            font-size: 20px;
        }

        .print-report-header small {
            display: block;
            font-size: 11px;
        }

        .print-report-title {
            text-align: center;
        }

        .print-report-title strong {
            font-size: 18px;
        }

        .print-supplier-details {
            display: grid !important;
            grid-template-columns: 80px 1fr;
            gap: 2px 8px;
            width: fit-content;
            margin: 0 auto 14px;
            font-size: 11px;
            color: #000 !important;
        }

        .print-supplier-details strong {
            font-weight: 600;
        }

        .print-report-footer {
            display: block !important;
            position: fixed;
            left: 0;
            right: 0;
            bottom: 8mm;
            border-top: 1px solid #000;
            padding-top: 6px;
            text-align: center;
            font-size: 11px;
            color: #000 !important;
        }

        .no-print {
            display: none !important;
        }

        .print-summary {
            display: none !important;
        }

        .supplier-history-tabs {
            display: none !important;
        }

        .print-action {
            display: none !important;
        }

        body {
            background: #fff !important;
        }
    }
</style>

<div class="container-fluid py-4">
    @php($company = \App\Models\Company::first())
    <div class="print-report-header">
        <div>
            <strong>{{ $company?->name ?? 'DeraNexa' }}</strong>
            <small>{{ implode(' / ', array_filter([$company?->phone, $company?->additional_number])) }}</small>
        </div>
        <div class="print-report-title">
            <strong>Supplier Account</strong>
            <small>Date: {{ now()->format('d M Y') }}</small>
        </div>
    </div>

    <div class="print-supplier-details">
        <span>Supplier</span><strong>{{ $supplier->name }}</strong>
        <span>Company</span><strong>{{ $supplier->company_name ?: '-' }}</strong>
        <span>Phone</span><strong>{{ $supplier->phone ?: '-' }}</strong>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 no-print supplier-history-heading">
        <div>
            <h1 class="h3 mb-0 fw-bold" style="color: #1f2937;">Payment History</h1>
            <p class="text-muted mb-0 mt-1">Complete financial transaction history for this supplier</p>
        </div>
        <div class="d-flex gap-2 supplier-history-actions">
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Suppliers
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4 print-summary supplier-history-summary" style="border-radius: 12px;">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><small class="text-muted d-block">Supplier</small><strong>{{ $supplier->name }}</strong></div>
                <div class="col-md-4"><small class="text-muted d-block">Company</small><span>{{ $supplier->company_name ?: '-' }}</span></div>
                <div class="col-md-4"><small class="text-muted d-block">Phone</small><span>{{ $supplier->phone ?: '-' }}</span></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4 print-summary supplier-history-metrics">
        <div class="col-md-6 col-xl-2">
            <div class="card border-start border-4 border-dark shadow-sm h-100"><div class="card-body"><small class="text-muted">Total Purchases</small><h4 class="mb-0 mt-1">Rs. {{ number_format($summary['totalPurchases'], 0) }}</h4></div></div>
        </div>
        <div class="col-md-6 col-xl-2">
            <div class="card border-start border-4 border-success shadow-sm h-100"><div class="card-body"><small class="text-muted">Total Paid</small><h4 class="mb-0 mt-1 text-success">Rs. {{ number_format($summary['totalPaid'], 0) }}</h4></div></div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-start border-4 border-warning shadow-sm h-100"><div class="card-body"><small class="text-muted">Total Payable / Due</small><h4 class="mb-0 mt-1 text-warning">Rs. {{ number_format($summary['totalPayable'], 0) }}</h4></div></div>
        </div>
        <div class="col-md-6 col-xl-2">
            <div class="card border-start border-4 border-info shadow-sm h-100"><div class="card-body"><small class="text-muted">Purchase Returns</small><h4 class="mb-0 mt-1 text-info">Rs. {{ number_format($summary['totalReturns'], 0) }}</h4></div></div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-start border-4 border-danger shadow-sm h-100"><div class="card-body"><small class="text-muted">Current Supplier Balance</small><h4 class="mb-0 mt-1 text-danger">Rs. {{ number_format($summary['currentBalance'], 0) }}</h4></div></div>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-header bg-white border-bottom-0 pt-3 px-3 supplier-history-tabs">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#purchase-payments" type="button" role="tab">Purchase Payments</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#supplier-payables" type="button" role="tab">Supplier Payables</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#purchase-returns" type="button" role="tab">Purchase Returns</button></li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="purchase-payments" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Date</th><th>Purchase / Invoice</th><th>Total Purchase</th><th>Paid Amount</th><th class="text-center print-action">Action</th></tr></thead>
                            <tbody>
                            @forelse($payments as $payment)
                                <tr>
                                    <td>{{ optional($payment->payment_date)->format('d M Y') }}</td>
                                    <td>{{ $payment->purchase?->purchase_number ?: '-' }}<br><small class="text-muted">{{ $payment->payment_number }}</small></td>
                                    <td>Rs. {{ number_format((float) ($payment->purchase?->total_amount ?? 0), 0) }}</td>
                                    <td class="text-success">Rs. {{ number_format((float) $payment->amount, 0) }}</td>
                                    <td class="text-center print-action"><a href="{{ $payment->purchase ? route('admin.purchases.show', $payment->purchase) : '#' }}" class="btn btn-sm btn-outline-primary" title="View Purchase"><i class="bi bi-eye"></i></a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No purchase payments found.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="supplier-payables" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Date</th><th>Reference / Invoice</th><th>Payable Amount</th><th>Paid Amount</th></tr></thead>
                            <tbody>
                            @forelse($payablePayments as $payment)
                                <tr>
                                    <td>{{ optional($payment->payment_date)->format('d M Y') }}</td>
                                    <td>{{ $payment->purchase?->purchase_number ?: '-' }}<br><small class="text-muted">{{ $payment->payment_number }}</small></td>
                                    <td>Rs. {{ number_format((float) ($payment->purchase?->total_amount ?? 0), 0) }}</td>
                                    <td class="text-success">Rs. {{ number_format((float) $payment->amount, 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No supplier payable history found.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="purchase-returns" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Date</th><th>Purchase / Return Number</th><th>Return Amount</th><th class="text-center print-action">Action</th></tr></thead>
                            <tbody>
                            @forelse($returns as $return)
                                <tr>
                                    <td>{{ optional($return->return_date)->format('d M Y') }}</td>
                                    <td>{{ $return->purchase?->purchase_number ?: '-' }}<br><small class="text-muted">{{ $return->return_number }}</small></td>
                                    <td>Rs. {{ number_format((float) $return->total_amount, 0) }}</td>
                                    <td class="text-center print-action"><a href="{{ route('admin.purchase-returns.show', $return) }}" class="btn btn-sm btn-outline-primary" title="View Return"><i class="bi bi-eye"></i></a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No purchase returns found.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="print-report-footer">
        {{ $company?->address ?? 'Dera Ismail Khan' }}
    </div>
</div>
@endsection
