@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Supplier Payment History</h1>
            <p class="text-muted mb-0">
                <strong>{{ $supplier->name }}</strong>
                @if($supplier->company_name) &nbsp;·&nbsp; {{ $supplier->company_name }} @endif
                @if($supplier->phone) &nbsp;·&nbsp; {{ $supplier->phone }} @endif
                <span class="badge bg-{{ $supplier->status === 'active' ? 'success' : 'warning' }} ms-2">
                    {{ ucfirst($supplier->status) }}
                </span>
            </p>
        </div>
        <div class="d-flex gap-2 no-print">
            <a href="{{ route('admin.reports.supplier.outstanding') }}"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <a href="{{ route('admin.reports.supplier.ledger', $supplier) }}"
               class="btn btn-outline-info btn-sm">
                <i class="bi bi-journal-text me-1"></i>View Ledger
            </a>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print
            </button>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="card mb-4 no-print">
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
            <span class="fw-semibold"><i class="bi bi-funnel me-2"></i>Filters</span>
            <button class="btn btn-sm btn-link p-0 text-secondary" type="button"
                    data-bs-toggle="collapse" data-bs-target="#filterPanel">
                <i class="bi bi-chevron-down"></i>
            </button>
        </div>
        <div class="collapse show" id="filterPanel">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.supplier.payment-history', $supplier) }}">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Date From</label>
                            <input type="date" name="date_from" class="form-control form-control-sm"
                                   value="{{ $filters['date_from'] }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Date To</label>
                            <input type="date" name="date_to" class="form-control form-control-sm"
                                   value="{{ $filters['date_to'] }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Quick Range</label>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('today')">Today</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_week')">This Week</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_month')">This Month</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('last_month')">Last Month</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_year')">This Year</button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Payment Method</label>
                            <select name="payment_method" class="form-select form-select-sm">
                                <option value="">All Methods</option>
                                @foreach($paymentMethods as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ ($filters['payment_method'] ?? '') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Apply
                        </button>
                        <a href="{{ route('admin.reports.supplier.payment-history', $supplier) }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Payments Made</p>
                    <h4 class="mb-0 fw-bold text-success">Rs.&nbsp;{{ number_format($summary['total_payments'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Payment Count</p>
                    <h4 class="mb-0 fw-bold text-primary">{{ $summary['payment_count'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Average Payment</p>
                    <h5 class="mb-0 fw-bold text-info">Rs.&nbsp;{{ number_format($summary['avg_payment'], 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Current Outstanding</p>
                    <h5 class="mb-0 fw-bold text-danger">
                        Rs.&nbsp;{{ number_format(
                            \App\Models\Purchase::where('supplier_id', $supplier->id)
                                ->where('status', '!=', 'cancelled')
                                ->sum(\Illuminate\Support\Facades\DB::raw('total_amount - paid_amount')),
                        2) }}
                    </h5>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- Method Breakdown --}}
        @if($summary['method_breakdown']->count() > 0)
        <div class="col-lg-3 no-print">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold">Payment Methods</h6>
                </div>
                <div class="card-body p-0">
                    @php $total = $summary['total_payments']; @endphp
                    @foreach($summary['method_breakdown'] as $method)
                    @php
                        $pct   = $total > 0 ? round($method->total / $total * 100, 1) : 0;
                        $mc    = ['cash'=>'success','bank_transfer'=>'info','easypaisa'=>'warning',
                                  'jazz_cash'=>'warning','cheque'=>'secondary','other'=>'light'];
                        $color = $mc[$method->payment_method] ?? 'secondary';
                    @endphp
                    <div class="px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="badge bg-{{ $color }} {{ in_array($color,['warning','light'])?'text-dark':'' }}">
                                    {{ \App\Models\PurchasePayment::$methods[$method->payment_method] ?? ucfirst($method->payment_method) }}
                                </span>
                                <small class="text-muted ms-1">{{ $method->count }}x</small>
                            </div>
                            <div class="text-end">
                                <div class="small fw-semibold">Rs.&nbsp;{{ number_format($method->total, 2) }}</div>
                                <small class="text-muted">{{ $pct }}%</small>
                            </div>
                        </div>
                        <div class="progress mt-1" style="height:4px">
                            <div class="progress-bar bg-{{ $color }}" style="width:{{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Transaction Table --}}
        <div class="{{ $summary['method_breakdown']->count() > 0 ? 'col-lg-9' : 'col-12' }}">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Payment Transactions</h5>
                    <span class="text-muted small">{{ $history->total() }} record(s)</span>
                </div>
                <div class="card-body p-0">
                    @if($history->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Date</th>
                                    <th>Payment #</th>
                                    <th>Purchase #</th>
                                    <th class="text-end">Amount Paid</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th>Recorded By</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($history as $payment)
                                @php
                                    $mc    = ['cash'=>'success','bank_transfer'=>'info','easypaisa'=>'warning',
                                              'jazz_cash'=>'warning','cheque'=>'secondary','other'=>'light'];
                                    $color = $mc[$payment->payment_method] ?? 'secondary';
                                @endphp
                                <tr>
                                    <td class="ps-3 text-muted">
                                        {{ ($history->currentPage() - 1) * $history->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="text-nowrap">{{ $payment->payment_date->format('d M Y') }}</td>
                                    <td><code class="small">{{ $payment->payment_number }}</code></td>
                                    <td>
                                        @if($payment->purchase)
                                            <a href="{{ route('admin.purchases.show', $payment->purchase_id) }}"
                                               class="text-primary small">
                                                {{ $payment->purchase->purchase_number }}
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        Rs.&nbsp;{{ number_format($payment->amount, 2) }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $color }} {{ in_array($color,['warning','light'])?'text-dark':'' }}">
                                            {{ \App\Models\PurchasePayment::$methods[$payment->payment_method] ?? ucfirst($payment->payment_method) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $payment->reference_number ?? '—' }}</small>
                                    </td>
                                    <td>
                                        @if($payment->recorder)
                                            <small>{{ $payment->recorder->name }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $payment->notes
                                                ? \Illuminate\Support\Str::limit($payment->notes, 35)
                                                : '—' }}
                                        </small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-semibold">
                                <tr>
                                    <td colspan="4" class="ps-3 text-end">Page Total:</td>
                                    <td class="text-end text-success">
                                        Rs.&nbsp;{{ number_format($history->getCollection()->sum('amount'), 2) }}
                                    </td>
                                    <td colspan="4"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="card-footer bg-white no-print">
                        {{ $history->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-cash-stack text-muted" style="font-size:3rem"></i>
                        <h5 class="mt-3 text-muted">No Payments Found</h5>
                        <p class="text-muted">No payment transactions match the selected period.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- row --}}
</div>
@endsection

@push('styles')
<style>
.btn-xs   { padding:.2rem .45rem; font-size:.75rem; }
.border-3 { border-width:3px !important; }
code.small { font-size:.8rem; background:#f8f9fa; padding:.1rem .3rem; border-radius:.25rem; }
@media print {
    * { color: #000000 !important; }
    .no-print { display:none !important; }
    .card { border:none !important; box-shadow:none !important; }
}
    .table { font-size:10px; }
    body  { font-size:11px; }
}
</style>
@endpush

@push('scripts')
<script>
function setRange(preset) {
    const df = document.querySelector('[name=date_from]');
    const dt = document.querySelector('[name=date_to]');
    const today = new Date();
    const fmt = d => d.toISOString().slice(0,10);
    switch(preset) {
        case 'today':     df.value = dt.value = fmt(today); break;
        case 'this_week': {
            const mon = new Date(today);
            mon.setDate(today.getDate() - (today.getDay()===0?6:today.getDay()-1));
            df.value = fmt(mon); dt.value = fmt(today); break;
        }
        case 'this_month':
            df.value = fmt(new Date(today.getFullYear(), today.getMonth(), 1));
            dt.value = fmt(today); break;
        case 'last_month':
            df.value = fmt(new Date(today.getFullYear(), today.getMonth()-1, 1));
            dt.value = fmt(new Date(today.getFullYear(), today.getMonth(), 0)); break;
        case 'this_year':
            df.value = fmt(new Date(today.getFullYear(), 0, 1));
            dt.value = fmt(today); break;
    }
}
</script>
@endpush
