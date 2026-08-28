@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Daily Purchase Report</h1>
            <p class="text-muted mb-0">Purchase order transactions for the selected period</p>
        </div>
        <div class="d-flex gap-2 no-print">
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print
            </button>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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
                <form method="GET" action="{{ route('admin.reports.purchase.daily') }}">
                    <div class="row g-3">

                        {{-- Date From --}}
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Date From</label>
                            <input type="date" name="date_from" class="form-control form-control-sm"
                                   value="{{ $filters['date_from'] ?? now()->toDateString() }}">
                        </div>

                        {{-- Date To --}}
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Date To</label>
                            <input type="date" name="date_to" class="form-control form-control-sm"
                                   value="{{ $filters['date_to'] ?? now()->toDateString() }}">
                        </div>

                        {{-- Date Presets --}}
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Quick Range</label>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('today')">Today</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('yesterday')">Yesterday</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_week')">This Week</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_month')">This Month</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('last_month')">Last Month</button>
                            </div>
                        </div>

                        {{-- Warehouse --}}
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

                        {{-- Supplier --}}
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Supplier</label>
                            <select name="supplier_id" class="form-select form-select-sm">
                                <option value="">All Suppliers</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}"
                                        {{ ($filters['supplier_id'] ?? '') == $sup->id ? 'selected' : '' }}>
                                        {{ $sup->name }}
                                    </option>
                                @endforeach
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

                        {{-- Purchase Status --}}
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="draft"     {{ ($filters['status'] ?? '') === 'draft'     ? 'selected' : '' }}>Draft</option>
                                <option value="confirmed" {{ ($filters['status'] ?? '') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        {{-- Search --}}
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="PO number or supplier name…"
                                   value="{{ $filters['search'] ?? '' }}">
                        </div>

                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Apply Filters
                        </button>
                        <a href="{{ route('admin.reports.purchase.daily') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Summary Cards ── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Purchase Amount</p>
                    <h5 class="mb-0 text-primary fw-bold">Rs.&nbsp;{{ number_format($summary['total_amount'], 2) }}</h5>
                    <small class="text-muted">excl. cancelled</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Paid</p>
                    <h5 class="mb-0 text-success fw-bold">Rs.&nbsp;{{ number_format($summary['total_paid'], 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Payable</p>
                    <h5 class="mb-0 text-danger fw-bold">Rs.&nbsp;{{ number_format($summary['total_payable'], 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Discount</p>
                    <h5 class="mb-0 text-warning fw-bold">Rs.&nbsp;{{ number_format($summary['total_discount'], 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Number of POs</p>
                    <h5 class="mb-0 fw-bold">{{ number_format($summary['po_count']) }}</h5>
                    <small class="text-muted">
                        {{ $summary['confirmed_count'] }} confirmed &middot;
                        {{ $summary['draft_count'] }} draft &middot;
                        {{ $summary['cancelled_count'] }} cancelled
                    </small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Avg Purchase Value</p>
                    <h5 class="mb-0 text-info fw-bold">Rs.&nbsp;{{ number_format($summary['avg_purchase_value'], 2) }}</h5>
                    <small class="text-muted">per confirmed PO</small>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Data Table ── --}}
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Purchase Orders</h5>
            <span class="text-muted small">{{ $report->total() }} record(s)</span>
        </div>
        <div class="card-body p-0">
            @if($report->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">PO Number</th>
                            <th>Date</th>
                            <th>Supplier</th>
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
                        @foreach($report as $purchase)
                        @php
                            $payable = $purchase->total_amount - $purchase->paid_amount;
                            $isCancelled = $purchase->status === 'cancelled';
                        @endphp
                        <tr class="{{ $isCancelled ? 'text-muted' : '' }}">
                            <td class="ps-3">
                                <span class="{{ $isCancelled ? 'text-decoration-line-through' : 'fw-semibold text-primary' }}">
                                    {{ $purchase->purchase_number }}
                                </span>
                            </td>
                            <td>{{ $purchase->purchase_date->format('d M Y') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $purchase->supplier->name }}</div>
                                @if($purchase->supplier->company_name)
                                    <small class="text-muted">{{ $purchase->supplier->company_name }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $purchase->warehouse->name }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $purchase->total_items }}</span>
                            </td>
                            <td class="text-end">{{ number_format($purchase->subtotal, 2) }}</td>
                            <td class="text-end text-danger">
                                {{ $purchase->discount > 0 ? number_format($purchase->discount, 2) : '—' }}
                            </td>
                            <td class="text-end fw-semibold">{{ number_format($purchase->total_amount, 2) }}</td>
                            <td class="text-end text-success">{{ number_format($purchase->paid_amount, 2) }}</td>
                            <td class="text-end {{ $payable > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">
                                {{ number_format($payable, 2) }}
                            </td>
                            <td>
                                @if($purchase->payment_status === 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($purchase->payment_status === 'partial')
                                    <span class="badge bg-warning text-dark">Partial</span>
                                @else
                                    <span class="badge bg-danger">Unpaid</span>
                                @endif
                            </td>
                            <td>
                                @if($purchase->status === 'confirmed')
                                    <span class="badge bg-success">Confirmed</span>
                                @elseif($purchase->status === 'draft')
                                    <span class="badge bg-warning text-dark">Draft</span>
                                @else
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                            <td class="text-center no-print">
                                <a href="{{ route('admin.purchases.show', $purchase) }}"
                                   class="btn btn-xs btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="5" class="ps-3 text-end">Page Totals (excl. cancelled):</td>
                            <td class="text-end">
                                Rs.&nbsp;{{ number_format($report->getCollection()->where('status','!=','cancelled')->sum('subtotal'), 2) }}
                            </td>
                            <td class="text-end text-danger">
                                Rs.&nbsp;{{ number_format($report->getCollection()->where('status','!=','cancelled')->sum('discount'), 2) }}
                            </td>
                            <td class="text-end">
                                Rs.&nbsp;{{ number_format($report->getCollection()->where('status','!=','cancelled')->sum('total_amount'), 2) }}
                            </td>
                            <td class="text-end text-success">
                                Rs.&nbsp;{{ number_format($report->getCollection()->where('status','!=','cancelled')->sum('paid_amount'), 2) }}
                            </td>
                            <td class="text-end text-danger">
                                Rs.&nbsp;{{ number_format($report->getCollection()->where('status','!=','cancelled')->sum(fn($p) => $p->total_amount - $p->paid_amount), 2) }}
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
                <i class="bi bi-cart-plus text-muted" style="font-size:3rem"></i>
                <h5 class="mt-3 text-muted">No Purchases Found</h5>
                <p class="text-muted">No purchase orders match the selected filters.</p>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
.btn-xs { padding:.2rem .45rem; font-size:.75rem; }
@media print {
    .no-print { display:none !important; }
    .card { border:none !important; box-shadow:none !important; }
    .table { font-size:11px; }
    body { font-size:12px; }
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
        case 'today':
            df.value = dt.value = fmt(today); break;
        case 'yesterday': {
            const y = new Date(today); y.setDate(y.getDate()-1);
            df.value = dt.value = fmt(y); break;
        }
        case 'this_week': {
            const mon = new Date(today);
            mon.setDate(today.getDate() - (today.getDay() === 0 ? 6 : today.getDay()-1));
            df.value = fmt(mon); dt.value = fmt(today); break;
        }
        case 'this_month':
            df.value = fmt(new Date(today.getFullYear(), today.getMonth(), 1));
            dt.value = fmt(today); break;
        case 'last_month': {
            const f = new Date(today.getFullYear(), today.getMonth()-1, 1);
            const l = new Date(today.getFullYear(), today.getMonth(), 0);
            df.value = fmt(f); dt.value = fmt(l); break;
        }
    }
}
</script>
@endpush
