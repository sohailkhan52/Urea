@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Page Header ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Invoice Report</h1>
            <p class="text-muted mb-0">Unified view of sales invoices and purchase orders</p>
        </div>
        <div class="d-flex gap-2 no-print">
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print
            </button>
        </div>
    </div>

    {{-- ── Filter Panel ── --}}
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
                <form method="GET" action="{{ route('admin.reports.invoices') }}">
                    <div class="row g-3">

                        {{-- Type toggle --}}
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Invoice Type</label>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="type" id="type_both"
                                       value="" {{ ($filters['type'] ?? '') === '' ? 'checked' : '' }}>
                                <label class="btn btn-sm btn-outline-secondary" for="type_both">
                                    <i class="bi bi-journals me-1"></i>Both
                                </label>

                                <input type="radio" class="btn-check" name="type" id="type_sales"
                                       value="sales" {{ ($filters['type'] ?? '') === 'sales' ? 'checked' : '' }}>
                                <label class="btn btn-sm btn-outline-primary" for="type_sales">
                                    <i class="bi bi-receipt me-1"></i>Sales Only
                                </label>

                                <input type="radio" class="btn-check" name="type" id="type_purchases"
                                       value="purchases" {{ ($filters['type'] ?? '') === 'purchases' ? 'checked' : '' }}>
                                <label class="btn btn-sm btn-outline-warning" for="type_purchases">
                                    <i class="bi bi-cart-plus me-1"></i>Purchases Only
                                </label>
                            </div>
                        </div>

                        {{-- Date From --}}
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Date From</label>
                            <input type="date" name="date_from" class="form-control form-control-sm"
                                   value="{{ $filters['date_from'] ?? now()->startOfMonth()->toDateString() }}">
                        </div>

                        {{-- Date To --}}
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Date To</label>
                            <input type="date" name="date_to" class="form-control form-control-sm"
                                   value="{{ $filters['date_to'] ?? now()->toDateString() }}">
                        </div>

                        {{-- Quick presets --}}
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Quick Range</label>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('today')">Today</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('yesterday')">Yesterday</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_week')">This Week</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_month')">This Month</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('last_month')">Last Month</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_year')">This Year</button>
                            </div>
                        </div>

                        {{-- Warehouse (super admin only — admin's warehouse enforced in backend) --}}
                        @if(auth()->user()->isSuperAdmin())
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Warehouse</label>
                            <select name="warehouse_id" class="form-select form-select-sm">
                                <option value="">All Warehouses</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}"
                                        {{ ($filters['warehouse_id'] ?? '') == $wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        {{-- Status
                             Sales:     draft | confirmed | cancelled
                             Purchases: draft | confirmed | cancelled
                             Both share the same enum — unified filter works correctly. --}}
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="draft"     {{ ($filters['status'] ?? '') === 'draft'     ? 'selected' : '' }}>Draft</option>
                                <option value="confirmed" {{ ($filters['status'] ?? '') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        {{-- Payment Status --}}
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Payment Status</label>
                            <select name="payment_status" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="unpaid"  {{ ($filters['payment_status'] ?? '') === 'unpaid'  ? 'selected' : '' }}>Unpaid</option>
                                <option value="partial" {{ ($filters['payment_status'] ?? '') === 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="paid"    {{ ($filters['payment_status'] ?? '') === 'paid'    ? 'selected' : '' }}>Paid</option>
                            </select>
                        </div>

                        {{-- Search --}}
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Invoice/PO number or party name…"
                                   value="{{ $filters['search'] ?? '' }}">
                        </div>

                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Apply Filters
                        </button>
                        <a href="{{ route('admin.reports.invoices') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Summary Cards ── --}}
    <div class="row g-3 mb-4">

        {{-- Sales invoices count + amount --}}
        @if(($filters['type'] ?? '') !== 'purchases')
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Sales Invoices</p>
                    <h4 class="mb-0 fw-bold text-primary">{{ number_format($summary['total_sales_invoices']) }}</h4>
                    <small class="text-muted">confirmed, non-cancelled</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Sales Amount</p>
                    <h5 class="mb-0 fw-bold text-primary">Rs.&nbsp;{{ number_format($summary['total_sales_amount'], 2) }}</h5>
                    <small class="text-success">Paid: Rs.&nbsp;{{ number_format($summary['total_sales_paid'], 2) }}</small>
                </div>
            </div>
        </div>
        @endif

        {{-- Purchase POs count + amount --}}
        @if(($filters['type'] ?? '') !== 'sales')
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Purchase Orders</p>
                    <h4 class="mb-0 fw-bold text-warning">{{ number_format($summary['total_purchase_invoices']) }}</h4>
                    <small class="text-muted">confirmed, non-cancelled</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Purchase Amount</p>
                    <h5 class="mb-0 fw-bold text-warning">Rs.&nbsp;{{ number_format($summary['total_purchase_amount'], 2) }}</h5>
                    <small class="text-success">Paid: Rs.&nbsp;{{ number_format($summary['total_purchase_paid'], 2) }}</small>
                </div>
            </div>
        </div>
        @endif

        {{-- Outstanding Receivables --}}
        @if(($filters['type'] ?? '') !== 'purchases')
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Outstanding Receivables</p>
                    <h5 class="mb-0 fw-bold text-danger">Rs.&nbsp;{{ number_format($summary['outstanding_receivables'], 2) }}</h5>
                    <small class="text-muted">customers owe you</small>
                </div>
            </div>
        </div>
        @endif

        {{-- Outstanding Payables --}}
        @if(($filters['type'] ?? '') !== 'sales')
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-orange border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Outstanding Payables</p>
                    <h5 class="mb-0 fw-bold" style="color:#e65100">Rs.&nbsp;{{ number_format($summary['outstanding_payables'], 2) }}</h5>
                    <small class="text-muted">you owe suppliers</small>
                </div>
            </div>
        </div>
        @endif

        {{-- Net Cash Flow (always shown) --}}
        @php $cashFlow = $summary['net_cash_flow']; @endphp
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start {{ $cashFlow >= 0 ? 'border-success' : 'border-danger' }} border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Net Cash Flow</p>
                    <h5 class="mb-0 fw-bold {{ $cashFlow >= 0 ? 'text-success' : 'text-danger' }}">
                        Rs.&nbsp;{{ number_format(abs($cashFlow), 2) }}
                    </h5>
                    <small class="text-muted">
                        {{ $cashFlow >= 0 ? 'Net positive (cash in > cash out)' : 'Net negative (cash out > cash in)' }}
                    </small>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Data Table ── --}}
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">
                @if(($filters['type'] ?? '') === 'sales')
                    Sales Invoices
                @elseif(($filters['type'] ?? '') === 'purchases')
                    Purchase Orders
                @else
                    All Invoices &amp; Purchase Orders
                @endif
            </h5>
            <span class="text-muted small">{{ $report->total() }} record(s)</span>
        </div>
        <div class="card-body p-0">
            @if($report->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Number</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Party</th>
                            <th>Warehouse</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Balance</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report as $row)
                        @php
                            $isSale       = $row->type === 'sale';
                            $isCancelled  = $row->status === 'cancelled';
                            $balance      = (float)$row->balance;
                        @endphp
                        <tr class="{{ $isCancelled ? 'text-muted' : '' }}">
                            <td class="ps-3">
                                <span class="{{ $isCancelled ? 'text-decoration-line-through' : ($isSale ? 'fw-semibold text-primary' : 'fw-semibold text-warning') }}">
                                    {{ $row->number }}
                                </span>
                            </td>
                            <td>
                                @if($isSale)
                                    <span class="badge bg-primary">
                                        <i class="bi bi-receipt me-1"></i>Sale
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-cart-plus me-1"></i>Purchase
                                    </span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $row->party_name }}</div>
                                @if($row->party_phone)
                                    <small class="text-muted">{{ $row->party_phone }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $row->warehouse_name }}</span>
                            </td>
                            <td class="text-end fw-semibold">
                                Rs.&nbsp;{{ number_format($row->total_amount, 2) }}
                            </td>
                            <td class="text-end text-success">
                                Rs.&nbsp;{{ number_format($row->paid_amount, 2) }}
                            </td>
                            <td class="text-end {{ $balance > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">
                                Rs.&nbsp;{{ number_format($balance, 2) }}
                            </td>
                            <td>
                                @if($row->payment_status === 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($row->payment_status === 'partial')
                                    <span class="badge bg-warning text-dark">Partial</span>
                                @else
                                    <span class="badge bg-danger">Unpaid</span>
                                @endif
                            </td>
                            <td>
                                @if($row->status === 'confirmed')
                                    <span class="badge bg-success">Confirmed</span>
                                @elseif($row->status === 'draft')
                                    <span class="badge bg-warning text-dark">Draft</span>
                                @else
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                            <td class="text-center no-print">
                                @if($isSale)
                                    {{-- View sale --}}
                                    <a href="{{ route('admin.sales.show', $row->source_id) }}"
                                       class="btn btn-xs btn-outline-primary me-1" title="View Invoice">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    {{-- Print: only confirmed sales have a printable invoice --}}
                                    @if($row->status === 'confirmed')
                                    <a href="{{ route('admin.sales.print-invoice', $row->source_id) }}"
                                       class="btn btn-xs btn-outline-success" title="Print Invoice"
                                       target="_blank">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    @endif
                                @else
                                    {{-- View purchase --}}
                                    <a href="{{ route('admin.purchases.show', $row->source_id) }}"
                                       class="btn btn-xs btn-outline-primary me-1" title="View PO">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    {{-- Print purchase PO --}}
                                    @if($row->status === 'confirmed')
                                    <a href="{{ route('admin.purchases.print', $row->source_id) }}"
                                       class="btn btn-xs btn-outline-success" title="Print PO"
                                       target="_blank">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="5" class="ps-3 text-end">Page Totals:</td>
                            <td class="text-end">
                                Rs.&nbsp;{{ number_format(collect($report->items())->sum('total_amount'), 2) }}
                            </td>
                            <td class="text-end text-success">
                                Rs.&nbsp;{{ number_format(collect($report->items())->sum('paid_amount'), 2) }}
                            </td>
                            <td class="text-end text-danger">
                                Rs.&nbsp;{{ number_format(collect($report->items())->sum('balance'), 2) }}
                            </td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="card-footer bg-white no-print">
                {{ $report->links() }}
            </div>

            @else
            <div class="text-center py-5">
                <i class="bi bi-receipt text-muted" style="font-size:3rem"></i>
                <h5 class="mt-3 text-muted">No Invoices Found</h5>
                <p class="text-muted">No records match the selected filters. Try widening the date range or changing the type filter.</p>
                <a href="{{ route('admin.reports.invoices') }}" class="btn btn-outline-primary btn-sm">
                    Reset Filters
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Financial note ── --}}
    <p class="text-muted small mt-3 no-print">
        <i class="bi bi-info-circle me-1"></i>
        <strong>Summary cards exclude cancelled records.</strong>
        Outstanding Receivables = sales <em>due_amount</em> (stored on each sale).
        Outstanding Payables = sum of (<em>total_amount − paid_amount</em>) on confirmed purchases.
        Net Cash Flow = cash collected from customers minus cash paid to suppliers.
    </p>

</div>
@endsection

@push('styles')
<style>
.btn-xs { padding:.2rem .45rem; font-size:.75rem; }
.border-3 { border-width: 3px !important; }
@media print {
    * { color: #000000 !important; }
    .no-print { display:none !important; }
    .card { border:none !important; box-shadow:none !important; }
}
    .table { font-size:11px; }
    body { font-size:12px; }
    /* Print header info */
    body::before {
        content: "Invoice Report — Printed: {{ now()->format('d M Y H:i') }}";
        display: block;
        font-size: 11px;
        color: #666;
        margin-bottom: 10px;
    }
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
        case 'today':      df.value = dt.value = fmt(today); break;
        case 'yesterday': {
            const y = new Date(today); y.setDate(y.getDate()-1);
            df.value = dt.value = fmt(y); break;
        }
        case 'this_week': {
            const mon = new Date(today);
            mon.setDate(today.getDate() - (today.getDay()===0 ? 6 : today.getDay()-1));
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
