@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1" id="reportTitle">Sales Report</h1>
            <p class="text-muted mb-0" id="reportSubtitle">View and analyze sales transactions by date range</p>
        </div>
        <div class="d-flex gap-2 no-print">
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
                <form method="GET" action="{{ route('admin.reports.sales.daily') }}" id="salesFiltersForm">
                    <input type="hidden" name="period" value="{{ $filters['period'] ?? '' }}" id="periodField">
                    <div class="row g-3">

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Date From</label>
                            <input type="date" name="date_from" class="form-control form-control-sm"
                                   value="{{ $filters['date_from'] ?? '' }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Date To</label>
                            <input type="date" name="date_to" class="form-control form-control-sm"
                                   value="{{ $filters['date_to'] ?? '' }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Quick Range</label>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('today')">Today</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('yesterday')">Yesterday</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_week')">This Week</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('last_week')">Last Week</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_month')">This Month</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('last_month')">Last Month</button>
                            </div>
                        </div>

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

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Customer</label>
                            <select name="customer_id" class="form-select form-select-sm">
                                <option value="">All Customers</option>
                                @foreach($customers as $cust)
                                    <option value="{{ $cust->id }}"
                                        {{ ($filters['customer_id'] ?? '') == $cust->id ? 'selected' : '' }}>
                                        {{ $cust->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All</option>
                                {{-- Correct enum values: draft|confirmed|cancelled --}}
                                <option value="draft"     {{ ($filters['status'] ?? '') === 'draft'     ? 'selected' : '' }}>Draft</option>
                                <option value="confirmed" {{ ($filters['status'] ?? '') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Payment Status</label>
                            <select name="payment_status" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="unpaid"  {{ ($filters['payment_status'] ?? '') === 'unpaid'  ? 'selected' : '' }}>Unpaid</option>
                                <option value="partial" {{ ($filters['payment_status'] ?? '') === 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="paid"    {{ ($filters['payment_status'] ?? '') === 'paid'    ? 'selected' : '' }}>Paid</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Invoice # or customer name…"
                                   value="{{ $filters['search'] ?? '' }}">
                        </div>

                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Apply Filters
                        </button>
                        <a href="{{ route('admin.reports.sales.daily') }}"
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
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Sales Amount</p>
                    <h5 class="mb-0 fw-bold text-primary">Rs.&nbsp;{{ number_format($summary['total_sales'], 2) }}</h5>
                    <small class="text-muted">excl. cancelled</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Paid</p>
                    <h5 class="mb-0 fw-bold text-success">Rs.&nbsp;{{ number_format($summary['total_paid'], 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Outstanding</p>
                    <h5 class="mb-0 fw-bold text-danger">Rs.&nbsp;{{ number_format($summary['total_due'], 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Discount</p>
                    <h5 class="mb-0 fw-bold text-warning">Rs.&nbsp;{{ number_format($summary['total_discount'], 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-secondary border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Invoices</p>
                    <h5 class="mb-0 fw-bold">{{ number_format($summary['total_count']) }}</h5>
                    <small class="text-muted">{{ $summary['completed_count'] }} confirmed</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Avg Sale Value</p>
                    <h5 class="mb-0 fw-bold text-info">Rs.&nbsp;{{ number_format($summary['average_sale_value'], 2) }}</h5>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sales Transactions</h5>
            <span class="text-muted small">{{ $report->total() }} record(s)</span>
        </div>
        <div class="card-body p-0">
            @if($report->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Invoice #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Warehouse</th>
                            <th class="text-center">Items</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Balance</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report as $sale)
                        @php
                            $isCancelled = $sale->status === 'cancelled';
                            // Handle walk-in sales with no related customer.
                            $customerName  = $sale->customer?->name ?? $sale->walkin_customer_name ?? 'Walk-in';
                            $customerPhone = $sale->customer?->phone ?? $sale->walkin_customer_contact ?? '—';
                        @endphp
                        <tr class="{{ $isCancelled ? 'text-muted' : '' }}">
                            <td class="ps-3">
                                <strong class="{{ $isCancelled ? 'text-decoration-line-through text-muted' : 'text-primary' }}">
                                    {{ $sale->invoice_number }}
                                </strong>
                            </td>
                            <td>{{ $sale->sale_date->format('d M Y') }}</td>
                            <td>
                                <div>{{ $customerName }}</div>
                                <small class="text-muted">{{ $customerPhone }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $sale->warehouse->name }}</span>
                            </td>
                            <td class="text-center">
                                {{-- items_count loaded via withCount — no N+1 --}}
                                <span class="badge bg-light text-dark border">{{ $sale->items_count }}</span>
                            </td>
                            <td class="text-end">Rs.&nbsp;{{ number_format($sale->subtotal, 2) }}</td>
                            <td class="text-end text-danger">
                                {{ $sale->discount > 0 ? 'Rs. '.number_format($sale->discount, 2) : '—' }}
                            </td>
                            <td class="text-end fw-semibold">Rs.&nbsp;{{ number_format($sale->total_amount, 2) }}</td>
                            <td class="text-end text-success">Rs.&nbsp;{{ number_format($sale->paid_amount, 2) }}</td>
                            <td class="text-end {{ $sale->due_amount > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">
                                Rs.&nbsp;{{ number_format($sale->due_amount, 2) }}
                            </td>
                            <td>
                                @if($sale->payment_status === 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($sale->payment_status === 'partial')
                                    <span class="badge bg-warning text-dark">Partial</span>
                                @else
                                    <span class="badge bg-danger">Unpaid</span>
                                @endif
                            </td>
                            <td>
                                @if($sale->status === 'confirmed')
                                    <span class="badge bg-success">Confirmed</span>
                                @elseif($sale->status === 'draft')
                                    <span class="badge bg-warning text-dark">Draft</span>
                                @else
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                            <td class="text-center no-print">
                                <a href="{{ route('admin.sales.show', $sale) }}"
                                   class="btn btn-xs btn-outline-primary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        @php
                            $nonCancelled = $report->getCollection()->where('status', '!=', 'cancelled');
                        @endphp
                        <tr>
                            <td colspan="5" class="ps-3 text-end">Page Totals (excl. cancelled):</td>
                            <td class="text-end">Rs.&nbsp;{{ number_format($nonCancelled->sum('subtotal'), 2) }}</td>
                            <td class="text-end text-danger">Rs.&nbsp;{{ number_format($nonCancelled->sum('discount'), 2) }}</td>
                            <td class="text-end">Rs.&nbsp;{{ number_format($nonCancelled->sum('total_amount'), 2) }}</td>
                            <td class="text-end text-success">Rs.&nbsp;{{ number_format($nonCancelled->sum('paid_amount'), 2) }}</td>
                            <td class="text-end text-danger">Rs.&nbsp;{{ number_format($nonCancelled->sum('due_amount'), 2) }}</td>
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
                <h5 class="mt-3 text-muted">No Sales Found</h5>
                <p class="text-muted">No sales transactions match the selected filters.</p>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
.btn-xs   { padding:.2rem .45rem; font-size:.75rem; }
.border-3 { border-width:3px !important; }
@media print {
    * { color: #000000 !important; }
    .no-print { display:none !important; }
    .card { border:none !important; box-shadow:none !important; }
    .table { font-size:10px; }
    body { font-size:11px; background: white; }
    .text-danger, .text-success, .text-warning, .text-info, .text-primary, .text-secondary { color: #000000 !important; }
    thead { background-color: white !important; }
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
    const periodField = document.querySelector('#periodField');
    const today = new Date();
    const fmt = d => d.toISOString().slice(0,10);
    
    // Set the period field
    periodField.value = preset;
    
    switch(preset) {
        case 'today':     df.value = dt.value = fmt(today); break;
        case 'yesterday': { const y=new Date(today); y.setDate(y.getDate()-1); df.value=dt.value=fmt(y); break; }
        case 'this_week': {
            const mon=new Date(today); mon.setDate(today.getDate()-(today.getDay()===0?6:today.getDay()-1));
            df.value=fmt(mon); dt.value=fmt(today); break;
        }
        case 'last_week': {
            const thisMonday=new Date(today); thisMonday.setDate(today.getDate()-(today.getDay()===0?6:today.getDay()-1));
            const lastMonday=new Date(thisMonday); lastMonday.setDate(thisMonday.getDate()-7);
            const lastSunday=new Date(thisMonday); lastSunday.setDate(thisMonday.getDate()-1);
            df.value=fmt(lastMonday); dt.value=fmt(lastSunday); break;
        }
        case 'this_month':
            df.value=fmt(new Date(today.getFullYear(),today.getMonth(),1)); dt.value=fmt(today); break;
        case 'last_month':
            df.value=fmt(new Date(today.getFullYear(),today.getMonth()-1,1));
            dt.value=fmt(new Date(today.getFullYear(),today.getMonth(),0)); break;
    }
    
    // Submit the form
    document.getElementById('salesFiltersForm').submit();
}

// Update title based on selected period
function updateReportTitle() {
    const period = '{{ $filters["period"] ?? "" }}';
    const title = document.getElementById('reportTitle');
    const subtitle = document.getElementById('reportSubtitle');
    const dateFrom = '{{ $filters["date_from"] ?? "" }}';
    const dateTo = '{{ $filters["date_to"] ?? "" }}';
    
    if (!title || !subtitle) return;
    
    switch(period) {
        case 'today':
            title.textContent = 'Today Sales Report';
            subtitle.textContent = 'Sales transactions for today';
            break;
        case 'yesterday':
            title.textContent = 'Yesterday Sales Report';
            subtitle.textContent = 'Sales transactions for yesterday';
            break;
        case 'this_week':
            title.textContent = 'Weekly Sales Report';
            subtitle.textContent = 'Sales transactions for this week';
            break;
        case 'last_week':
            title.textContent = 'Last Week Sales Report';
            subtitle.textContent = 'Sales transactions for last week';
            break;
        case 'this_month':
            title.textContent = 'Monthly Sales Report';
            subtitle.textContent = 'Sales transactions for this month';
            break;
        case 'last_month':
            title.textContent = 'Last Month Sales Report';
            subtitle.textContent = 'Sales transactions for last month';
            break;
        default:
            title.textContent = 'Sales Report';
            if (period === '' && dateFrom !== '' && dateTo !== '') {
                subtitle.textContent = `Sales transactions from ${dateFrom} to ${dateTo}`;
            } else {
                subtitle.textContent = 'View and analyze sales transactions by date range';
            }
    }
}

// Call on page load
document.addEventListener('DOMContentLoaded', updateReportTitle);
</script>
@endpush
