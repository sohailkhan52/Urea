@extends('layouts.admin')

@section('title', 'Payment History - ' . $supplier->name)

@section('content')
<style>
    .print-report-header,
    .print-report-footer,
    .print-supplier-details {
        display: none;
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

    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h1 class="h3 mb-0 fw-bold" style="color: #1f2937;">Payment History - {{ $supplier->name }}</h1>
            <p class="text-muted mb-0 mt-1">Complete financial transaction history for this supplier</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Suppliers
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4 print-summary" style="border-radius: 12px;">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><small class="text-muted d-block">Supplier</small><strong>{{ $supplier->name }}</strong></div>
                <div class="col-md-4"><small class="text-muted d-block">Company</small><span>{{ $supplier->company_name ?: '-' }}</span></div>
                <div class="col-md-4"><small class="text-muted d-block">Phone</small><span>{{ $supplier->phone ?: '-' }}</span></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4 print-summary">
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
