@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Supplier Outstanding Payables</h1>
            <p class="text-muted mb-0">Suppliers with confirmed unpaid purchase balances</p>
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
                <form method="GET" action="{{ route('admin.reports.supplier.outstanding') }}">
                    <div class="row g-3">

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
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="active"   {{ ($filters['status'] ?? '') === 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Min. Payable</label>
                            <input type="number" name="min_balance" class="form-control form-control-sm"
                                   placeholder="e.g. 1000" min="0" step="0.01"
                                   value="{{ $filters['min_balance'] ?? '' }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Sort By</label>
                            <select name="sort_by" class="form-select form-select-sm">
                                <option value="balance"       {{ ($filters['sort_by'] ?? 'balance') === 'balance'       ? 'selected' : '' }}>Payable (High→Low)</option>
                                <option value="name"          {{ ($filters['sort_by'] ?? '') === 'name'          ? 'selected' : '' }}>Name (A→Z)</option>
                                <option value="last_purchase" {{ ($filters['sort_by'] ?? '') === 'last_purchase' ? 'selected' : '' }}>Last Purchase</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Name, company, phone or city…"
                                   value="{{ $filters['search'] ?? '' }}">
                        </div>

                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Apply
                        </button>
                        <a href="{{ route('admin.reports.supplier.outstanding') }}"
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
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Suppliers with Payables</p>
                    <h4 class="mb-0 fw-bold text-warning">{{ number_format($summary['total_suppliers']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Outstanding</p>
                    <h4 class="mb-0 fw-bold text-danger">Rs.&nbsp;{{ number_format($summary['total_outstanding'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Average Payable</p>
                    <h5 class="mb-0 fw-bold text-info">Rs.&nbsp;{{ number_format($summary['avg_outstanding'], 2) }}</h5>
                    <small class="text-muted">per supplier</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Overdue &gt;30 Days</p>
                    <h5 class="mb-0 fw-bold text-danger">Rs.&nbsp;{{ number_format($summary['overdue_30d'], 2) }}</h5>
                    <small class="text-muted">purchases older than 30 days</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- Top 10 Payables Panel --}}
        @if($summary['top10']->count() > 0)
        <div class="col-lg-4 no-print">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-trophy text-warning me-2"></i>Top 10 Payables
                    </h6>
                </div>
                <div class="card-body p-0">
                    @php $maxPayable = $summary['top10']->max('payable'); @endphp
                    @foreach($summary['top10'] as $i => $sup)
                    <div class="px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $i === 0 ? 'bg-danger' : ($i < 3 ? 'bg-warning text-dark' : 'bg-secondary') }} rounded-pill">
                                    {{ $i + 1 }}
                                </span>
                                <div>
                                    <div class="fw-semibold small">{{ $sup->name }}</div>
                                    <small class="text-muted">{{ $sup->phone }}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-danger small">Rs.&nbsp;{{ number_format($sup->payable, 2) }}</div>
                            </div>
                        </div>
                        <div class="progress mt-1" style="height:4px">
                            <div class="progress-bar bg-danger"
                                 style="width:{{ $maxPayable > 0 ? round($sup->payable / $maxPayable * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Main Table --}}
        <div class="{{ $summary['top10']->count() > 0 ? 'col-lg-8' : 'col-12' }}">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Outstanding Payables</h5>
                    <span class="text-muted small">{{ $report->total() }} supplier(s)</span>
                </div>
                <div class="card-body p-0">
                    @if($report->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Supplier</th>
                                    <th>Company</th>
                                    <th>Phone</th>
                                    <th class="text-center">POs</th>
                                    <th class="text-end">Total Purchased</th>
                                    <th class="text-end">Total Paid</th>
                                    <th class="text-end">Outstanding</th>
                                    <th>Last Purchase</th>
                                    <th class="text-center no-print">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report as $row)
                                @php
                                    $lastPurchase = $row->last_purchase_date
                                        ? \Carbon\Carbon::parse($row->last_purchase_date) : null;
                                    $daysSince = $lastPurchase ? $lastPurchase->diffInDays(now()) : null;
                                @endphp
                                <tr>
                                    <td class="ps-3 text-muted">
                                        {{ ($report->currentPage() - 1) * $report->perPage() + $loop->iteration }}
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $row->name }}</div>
                                        @if($row->city)
                                            <small class="text-muted">{{ $row->city }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $row->company_name ?? '—' }}</td>
                                    <td>{{ $row->phone ?? '—' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $row->total_pos }}</span>
                                    </td>
                                    <td class="text-end">
                                        Rs.&nbsp;{{ number_format($row->total_purchase_amount, 2) }}
                                    </td>
                                    <td class="text-end text-success">
                                        Rs.&nbsp;{{ number_format($row->total_paid, 2) }}
                                    </td>
                                    <td class="text-end fw-bold text-danger">
                                        Rs.&nbsp;{{ number_format($row->outstanding_payable, 2) }}
                                    </td>
                                    <td>
                                        @if($lastPurchase)
                                            {{ $lastPurchase->format('d M Y') }}
                                            <br><small class="text-muted">{{ $daysSince }}d ago</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center no-print">
                                        <a href="{{ route('admin.suppliers.show', $row->id) }}"
                                           class="btn btn-xs btn-outline-primary me-1" title="View Supplier">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.reports.supplier.ledger', $row->id) }}"
                                           class="btn btn-xs btn-outline-info me-1" title="Ledger">
                                            <i class="bi bi-journal-text"></i>
                                        </a>
                                        <a href="{{ route('admin.reports.supplier.payment-history', $row->id) }}"
                                           class="btn btn-xs btn-outline-success" title="Payments">
                                            <i class="bi bi-cash-stack"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-semibold">
                                <tr>
                                    <td colspan="4" class="ps-3 text-end">Page Totals:</td>
                                    <td class="text-center">{{ collect($report->items())->sum('total_pos') }}</td>
                                    <td class="text-end">Rs.&nbsp;{{ number_format(collect($report->items())->sum('total_purchase_amount'), 2) }}</td>
                                    <td class="text-end text-success">Rs.&nbsp;{{ number_format(collect($report->items())->sum('total_paid'), 2) }}</td>
                                    <td class="text-end text-danger">Rs.&nbsp;{{ number_format(collect($report->items())->sum('outstanding_payable'), 2) }}</td>
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
                        <h5 class="mt-3 text-muted">No Outstanding Payables</h5>
                        <p class="text-muted">No suppliers with outstanding balances match the current filters.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- row --}}

    <p class="text-muted small mt-3 no-print">
        <i class="bi bi-info-circle me-1"></i>
        Outstanding = <code>SUM(total_amount − paid_amount)</code> on confirmed, non-cancelled purchases.
        Overdue = outstanding on purchases older than 30 days.
        Warehouse filter applies to the purchases, not the supplier record.
    </p>

</div>
@endsection

@push('styles')
<style>
.btn-xs  { padding:.2rem .45rem; font-size:.75rem; }
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
