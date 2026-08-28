@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Supplier Ledger</h1>
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
            <a href="{{ route('admin.reports.supplier.payment-history', $supplier) }}"
               class="btn btn-outline-success btn-sm">
                <i class="bi bi-cash-stack me-1"></i>Payments
            </a>
            <a href="{{ route('admin.suppliers.show', $supplier) }}"
               class="btn btn-outline-primary btn-sm">
                <i class="bi bi-truck me-1"></i>Profile
            </a>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print
            </button>
        </div>
    </div>

    {{-- Date Range Filter --}}
    <div class="card mb-4 no-print">
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
            <span class="fw-semibold"><i class="bi bi-funnel me-2"></i>Date Range</span>
            <button class="btn btn-sm btn-link p-0 text-secondary" type="button"
                    data-bs-toggle="collapse" data-bs-target="#filterPanel">
                <i class="bi bi-chevron-down"></i>
            </button>
        </div>
        <div class="collapse show" id="filterPanel">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.supplier.ledger', $supplier) }}">
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
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">Quick Range</label>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_month')">This Month</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('last_month')">Last Month</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_quarter')">This Quarter</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_year')">This Year</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('all_time')">All Time</button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Apply
                        </button>
                        <a href="{{ route('admin.reports.supplier.ledger', $supplier) }}"
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
            <div class="card border-0 shadow-sm h-100 border-start border-secondary border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Opening Balance</p>
                    <h4 class="mb-0 fw-bold {{ $summary['opening_balance'] > 0 ? 'text-danger' : ($summary['opening_balance'] < 0 ? 'text-success' : '') }}">
                        Rs.&nbsp;{{ number_format(abs($summary['opening_balance']), 2) }}
                    </h4>
                    <small class="text-muted">
                        @if($summary['opening_balance'] > 0) Payable (we owe)
                        @elseif($summary['opening_balance'] < 0) Credit (overpaid)
                        @else Nil
                        @endif
                    </small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Payable Added</p>
                    <h4 class="mb-0 fw-bold text-warning">Rs.&nbsp;{{ number_format($summary['total_payable_added'], 2) }}</h4>
                    <small class="text-muted">Purchases &amp; adjustments</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Payments Made</p>
                    <h4 class="mb-0 fw-bold text-success">Rs.&nbsp;{{ number_format($summary['total_payment_made'], 2) }}</h4>
                    <small class="text-muted">Payments &amp; returns</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-{{ $summary['closing_balance'] > 0 ? 'danger' : 'success' }} border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Closing Balance</p>
                    <h4 class="mb-0 fw-bold {{ $summary['closing_balance'] > 0 ? 'text-danger' : 'text-success' }}">
                        Rs.&nbsp;{{ number_format(abs($summary['closing_balance']), 2) }}
                    </h4>
                    <small class="text-muted">
                        {{ $summary['closing_balance'] > 0 ? 'We owe supplier' : ($summary['closing_balance'] < 0 ? 'Supplier owes us' : 'Settled') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Ledger Table --}}
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">
                Account Statement
                <small class="text-muted fw-normal">
                    {{ \Carbon\Carbon::parse($filters['date_from'])->format('d M Y') }}
                    → {{ \Carbon\Carbon::parse($filters['date_to'])->format('d M Y') }}
                </small>
            </h5>
            <span class="text-muted small">{{ $summary['entry_count'] }} entries</span>
        </div>
        <div class="card-body p-0">

            {{-- Opening balance row --}}
            @if($summary['opening_balance'] != 0)
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr class="table-secondary">
                            <td class="ps-3 fw-semibold" colspan="4">
                                Opening Balance
                                <small class="text-muted fw-normal ms-2">
                                    brought forward before {{ \Carbon\Carbon::parse($filters['date_from'])->format('d M Y') }}
                                </small>
                            </td>
                            {{-- payable_added column --}}
                            <td class="text-end text-warning">
                                {{ $summary['opening_balance'] > 0 ? 'Rs. '.number_format($summary['opening_balance'], 2) : '—' }}
                            </td>
                            {{-- payment_made column --}}
                            <td class="text-end text-success">
                                {{ $summary['opening_balance'] < 0 ? 'Rs. '.number_format(abs($summary['opening_balance']), 2) : '—' }}
                            </td>
                            <td class="text-end fw-bold">
                                Rs.&nbsp;{{ number_format(abs($summary['opening_balance']), 2) }}
                                <small class="{{ $summary['opening_balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $summary['opening_balance'] > 0 ? 'Payable' : 'Credit' }}
                                </small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif

            @if($ledger->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Reference</th>
                            <th>Description</th>
                            {{--
                                Accounting convention for supplier ledger:
                                payable_added = Purchase charged to us  → shows as Debit   (we owe more)
                                payment_made  = Payment / Return credit → shows as Credit  (we owe less)
                                balance       = running payable balance (positive = we owe supplier)
                            --}}
                            <th class="text-end">Payable Added</th>
                            <th class="text-end">Payment Made</th>
                            <th class="text-end">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ledger as $entry)
                        @php
                            [$badgeClass, $typeLabel] = match($entry->type) {
                                'opening_balance' => ['bg-secondary',          'Opening Balance'],
                                'purchase'        => ['bg-warning text-dark',  'Purchase'],
                                'payment'         => ['bg-success',            'Payment'],
                                'return'          => ['bg-info text-dark',     'Return'],
                                'adjustment'      => ['bg-primary',            'Adjustment'],
                                default           => ['bg-secondary',          ucfirst($entry->type)],
                            };
                        @endphp
                        <tr>
                            <td class="ps-3 text-muted">
                                {{ ($ledger->currentPage() - 1) * $ledger->perPage() + $loop->iteration }}
                            </td>
                            <td class="text-nowrap">{{ $entry->date->format('d M Y') }}</td>
                            <td>
                                <span class="badge {{ $badgeClass }}">{{ $typeLabel }}</span>
                            </td>
                            <td>
                                @if($entry->reference_number)
                                    <code class="small">{{ $entry->reference_number }}</code>
                                @elseif($entry->purchase_id && $entry->purchase)
                                    <a href="{{ route('admin.purchases.show', $entry->purchase_id) }}"
                                       class="text-primary small">
                                        {{ $entry->purchase->purchase_number }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $entry->description ?? '—' }}</small>
                            </td>
                            <td class="text-end {{ $entry->payable_added > 0 ? 'text-warning fw-semibold' : 'text-muted' }}">
                                {{ $entry->payable_added > 0 ? 'Rs. '.number_format($entry->payable_added, 2) : '—' }}
                            </td>
                            <td class="text-end {{ $entry->payment_made > 0 ? 'text-success fw-semibold' : 'text-muted' }}">
                                {{ $entry->payment_made > 0 ? 'Rs. '.number_format($entry->payment_made, 2) : '—' }}
                            </td>
                            <td class="text-end fw-semibold">
                                Rs.&nbsp;{{ number_format(abs($entry->balance), 2) }}
                                @if($entry->balance > 0)
                                    <small class="text-danger">Payable</small>
                                @elseif($entry->balance < 0)
                                    <small class="text-success">Credit</small>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="5" class="ps-3 text-end">Period Totals:</td>
                            <td class="text-end text-warning">
                                Rs.&nbsp;{{ number_format($summary['total_payable_added'], 2) }}
                            </td>
                            <td class="text-end text-success">
                                Rs.&nbsp;{{ number_format($summary['total_payment_made'], 2) }}
                            </td>
                            <td class="text-end {{ $summary['closing_balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                Rs.&nbsp;{{ number_format(abs($summary['closing_balance']), 2) }}
                                {{ $summary['closing_balance'] > 0 ? 'Payable' : ($summary['closing_balance'] < 0 ? 'Credit' : '') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer bg-white no-print">
                {{ $ledger->links() }}
            </div>

            @else
            <div class="text-center py-5">
                <i class="bi bi-journal-text text-muted" style="font-size:3rem"></i>
                <h5 class="mt-3 text-muted">No Transactions Found</h5>
                <p class="text-muted">No ledger entries exist for this supplier in the selected period.</p>
                <a href="{{ route('admin.reports.supplier.ledger', $supplier) }}?date_from=2000-01-01&date_to={{ now()->toDateString() }}"
                   class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-clock-history me-1"></i>Show All Time
                </a>
            </div>
            @endif

        </div>
    </div>

    {{-- Closing balance reconciliation --}}
    @php
        $actualPayable = \App\Models\Purchase::where('supplier_id', $supplier->id)
            ->where('status', '!=', \App\Models\Purchase::STATUS_CANCELLED)
            ->sum(\Illuminate\Support\Facades\DB::raw('total_amount - paid_amount'));
    @endphp
    <div class="alert {{ abs($summary['closing_balance'] - $actualPayable) < 0.01 ? 'alert-success' : 'alert-warning' }} mt-3 no-print">
        <div class="d-flex align-items-center gap-2">
            <i class="bi {{ abs($summary['closing_balance'] - $actualPayable) < 0.01 ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }} flex-shrink-0"></i>
            <div>
                <strong>Balance Reconciliation:</strong>
                Ledger closing balance = <strong>Rs.&nbsp;{{ number_format($summary['closing_balance'], 2) }}</strong>
                &nbsp;|&nbsp;
                Actual payable (from purchases) = <strong>Rs.&nbsp;{{ number_format($actualPayable, 2) }}</strong>
                @if(abs($summary['closing_balance'] - $actualPayable) >= 0.01)
                    &nbsp;—&nbsp;
                    <span class="text-danger">
                        Difference: Rs.&nbsp;{{ number_format(abs($summary['closing_balance'] - $actualPayable), 2) }}
                    </span>
                    &mdash; the date filter may exclude some entries. Use "All Time" for full reconciliation.
                @else
                    &nbsp;—&nbsp; <span class="text-success">Fully reconciled ✓</span>
                @endif
            </div>
        </div>
    </div>

    <p class="text-muted small mt-2 no-print">
        <i class="bi bi-info-circle me-1"></i>
        <strong>Payable Added</strong> = purchase amount charged to us (increases what we owe).
        <strong>Payment Made</strong> = payment sent to supplier or purchase return credited (decreases what we owe).
        <strong>Closing Balance</strong> is the last <code>balance</code> value stored in the ledger.
        The reconciliation check compares it against <code>SUM(total_amount − paid_amount)</code> on confirmed purchases.
    </p>

</div>
@endsection

@push('styles')
<style>
.btn-xs   { padding:.2rem .45rem; font-size:.75rem; }
.border-3 { border-width:3px !important; }
code.small { font-size:.8rem; background:#f8f9fa; padding:.1rem .3rem; border-radius:.25rem; }
@media print {
    .no-print { display:none !important; }
    .card { border:none !important; box-shadow:none !important; }
    .table { font-size:10px; }
    body  { font-size:11px; }
    .alert { display:none !important; }
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
        case 'this_month':
            df.value = fmt(new Date(today.getFullYear(), today.getMonth(), 1));
            dt.value = fmt(today); break;
        case 'last_month':
            df.value = fmt(new Date(today.getFullYear(), today.getMonth()-1, 1));
            dt.value = fmt(new Date(today.getFullYear(), today.getMonth(), 0)); break;
        case 'this_quarter': {
            const q = Math.floor(today.getMonth() / 3);
            df.value = fmt(new Date(today.getFullYear(), q * 3, 1));
            dt.value = fmt(today); break;
        }
        case 'this_year':
            df.value = fmt(new Date(today.getFullYear(), 0, 1));
            dt.value = fmt(today); break;
        case 'all_time':
            df.value = '2000-01-01';
            dt.value = fmt(today); break;
    }
}
</script>
@endpush
