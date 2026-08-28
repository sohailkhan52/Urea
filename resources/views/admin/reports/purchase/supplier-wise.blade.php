@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Supplier-Wise Purchase Report</h1>
            <p class="text-muted mb-0">Purchase analysis grouped by supplier</p>
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
                <form method="GET" action="{{ route('admin.reports.purchase.supplier-wise') }}">
                    <div class="row g-3">

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Date From</label>
                            <input type="date" name="date_from" class="form-control form-control-sm"
                                   value="{{ $filters['date_from'] ?? now()->startOfMonth()->toDateString() }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Date To</label>
                            <input type="date" name="date_to" class="form-control form-control-sm"
                                   value="{{ $filters['date_to'] ?? now()->toDateString() }}">
                        </div>

                        {{-- Quick presets --}}
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

                        {{-- Warehouse (super admin only) --}}
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

                        {{-- Minimum Purchase Amount --}}
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Min Purchase Amount</label>
                            <input type="number" name="min_amount" class="form-control form-control-sm"
                                   placeholder="e.g. 10000" min="0" step="0.01"
                                   value="{{ $filters['min_amount'] ?? '' }}">
                        </div>

                        {{-- Search --}}
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Supplier name, company or phone…"
                                   value="{{ $filters['search'] ?? '' }}">
                        </div>

                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Apply Filters
                        </button>
                        <a href="{{ route('admin.reports.purchase.supplier-wise') }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Summary Cards ── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Suppliers</p>
                    <h4 class="mb-0 fw-bold">{{ number_format($summary['total_suppliers']) }}</h4>
                    <small class="text-muted">with confirmed POs</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Purchases</p>
                    <h4 class="mb-0 text-primary fw-bold">Rs.&nbsp;{{ number_format($summary['total_purchases'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Avg per Supplier</p>
                    <h4 class="mb-0 text-info fw-bold">Rs.&nbsp;{{ number_format($summary['avg_per_supplier'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Showing</p>
                    <h4 class="mb-0 fw-bold">{{ $report->total() }}</h4>
                    <small class="text-muted">supplier(s) on this filter</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Top 10 callout --}}
    @if(count($summary['top_supplier_ids']) > 0)
    <div class="alert alert-info d-flex align-items-center gap-2 no-print mb-4" role="alert">
        <i class="bi bi-trophy-fill flex-shrink-0"></i>
        <div>
            <strong>Top Suppliers by Purchase Volume</strong> are highlighted
            <span class="badge bg-warning text-dark ms-1">★ Top 5</span> in the table below.
        </div>
    </div>
    @endif

    {{-- ── Data Table ── --}}
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Supplier Purchase Summary</h5>
            <span class="text-muted small">{{ $report->total() }} supplier(s)</span>
        </div>
        <div class="card-body p-0">
            @if($report->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Supplier Name</th>
                            <th>Company</th>
                            <th>Phone</th>
                            <th class="text-center">Total POs</th>
                            <th class="text-end">Total Qty</th>
                            <th class="text-end">Purchase Amount</th>
                            <th class="text-end">Total Paid</th>
                            <th class="text-end">Outstanding</th>
                            <th class="text-end">Returns</th>
                            <th>Last Purchase</th>
                            <th class="no-print text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report as $i => $row)
                        @php
                            $isTop = in_array($row->id, $summary['top_supplier_ids']);
                        @endphp
                        <tr class="{{ $isTop ? 'table-warning' : '' }}">
                            <td class="ps-3 text-muted">
                                {{ ($report->currentPage() - 1) * $report->perPage() + $loop->iteration }}
                                @if($isTop) <span class="badge bg-warning text-dark ms-1">★</span> @endif
                            </td>
                            <td class="fw-semibold">{{ $row->name }}</td>
                            <td>{{ $row->company_name ?? '—' }}</td>
                            <td>{{ $row->phone ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $row->total_pos }}</span>
                            </td>
                            <td class="text-end">{{ number_format($row->total_quantity, 0) }}</td>
                            <td class="text-end fw-semibold">Rs.&nbsp;{{ number_format($row->total_amount, 2) }}</td>
                            <td class="text-end text-success">Rs.&nbsp;{{ number_format($row->total_paid, 2) }}</td>
                            <td class="text-end {{ $row->outstanding_payable > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">
                                Rs.&nbsp;{{ number_format($row->outstanding_payable, 2) }}
                            </td>
                            <td class="text-end text-warning">
                                {{ $row->total_returned > 0 ? 'Rs. '.number_format($row->total_returned, 2) : '—' }}
                            </td>
                            <td>
                                @if($row->last_purchase_date)
                                    {{ \Carbon\Carbon::parse($row->last_purchase_date)->format('d M Y') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center no-print">
                                <a href="{{ route('admin.suppliers.show', $row->id) }}"
                                   class="btn btn-xs btn-outline-primary" title="View Supplier">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="4" class="ps-3 text-end">Page Totals:</td>
                            <td class="text-center">{{ $report->getCollection()->sum('total_pos') }}</td>
                            <td class="text-end">{{ number_format($report->getCollection()->sum('total_quantity'), 0) }}</td>
                            <td class="text-end">Rs.&nbsp;{{ number_format($report->getCollection()->sum('total_amount'), 2) }}</td>
                            <td class="text-end text-success">Rs.&nbsp;{{ number_format($report->getCollection()->sum('total_paid'), 2) }}</td>
                            <td class="text-end text-danger">Rs.&nbsp;{{ number_format($report->getCollection()->sum('outstanding_payable'), 2) }}</td>
                            <td class="text-end text-warning">Rs.&nbsp;{{ number_format($report->getCollection()->sum('total_returned'), 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="card-footer bg-white no-print">
                {{ $report->links() }}
            </div>

            @else
            <div class="text-center py-5">
                <i class="bi bi-truck text-muted" style="font-size:3rem"></i>
                <h5 class="mt-3 text-muted">No Supplier Data Found</h5>
                <p class="text-muted">No confirmed purchases match the selected filters.</p>
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
    * { color: #000000 !important; }
    .no-print { display:none !important; }
    .card { border:none !important; box-shadow:none !important; }
    .table { font-size:10px; }
    body { font-size:11px; background: white; }
    .text-danger, .text-success, .text-warning, .text-info, .text-primary, .text-secondary { color: #000000 !important; }
    thead { background-color: white !important; }
}
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
        case 'today':      df.value = dt.value = fmt(today); break;
        case 'this_week': {
            const mon = new Date(today);
            mon.setDate(today.getDate() - (today.getDay()===0 ? 6 : today.getDay()-1));
            df.value = fmt(mon); dt.value = fmt(today); break;
        }
        case 'this_month':
            df.value = fmt(new Date(today.getFullYear(), today.getMonth(), 1));
            dt.value = fmt(today); break;
        case 'last_month': {
            df.value = fmt(new Date(today.getFullYear(), today.getMonth()-1, 1));
            dt.value = fmt(new Date(today.getFullYear(), today.getMonth(), 0)); break;
        }
        case 'this_year':
            df.value = fmt(new Date(today.getFullYear(), 0, 1));
            dt.value = fmt(today); break;
    }
}
</script>
@endpush
