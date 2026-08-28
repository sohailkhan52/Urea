@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Customer Outstanding Balances</h1>
            <p class="text-muted mb-0">Customers with confirmed unpaid sales balances</p>
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
                <form method="GET" action="{{ route('admin.reports.customer.outstanding') }}">
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
                            <label class="form-label small fw-semibold">Min. Balance</label>
                            <input type="number" name="min_balance" class="form-control form-control-sm"
                                   placeholder="e.g. 1000" min="0" step="0.01"
                                   value="{{ $filters['min_balance'] ?? '' }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Sort By</label>
                            <select name="sort_by" class="form-select form-select-sm">
                                <option value="balance"       {{ ($filters['sort_by'] ?? 'balance') === 'balance'       ? 'selected' : '' }}>Balance (High→Low)</option>
                                <option value="name"          {{ ($filters['sort_by'] ?? '') === 'name'          ? 'selected' : '' }}>Name (A→Z)</option>
                                <option value="last_purchase" {{ ($filters['sort_by'] ?? '') === 'last_purchase' ? 'selected' : '' }}>Last Purchase</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Name, phone or city…"
                                   value="{{ $filters['search'] ?? '' }}">
                        </div>

                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Apply
                        </button>
                        <a href="{{ route('admin.reports.customer.outstanding') }}"
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
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Customers with Balance</p>
                    <h4 class="mb-0 fw-bold text-primary">{{ number_format($summary['total_customers']) }}</h4>
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
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Average Outstanding</p>
                    <h5 class="mb-0 fw-bold text-warning">Rs.&nbsp;{{ number_format($summary['avg_outstanding'], 2) }}</h5>
                    <small class="text-muted">per customer</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Overdue &gt;30 Days</p>
                    <h5 class="mb-0 fw-bold text-danger">Rs.&nbsp;{{ number_format($summary['overdue_30d'], 2) }}</h5>
                    <small class="text-muted">sales older than 30 days</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- Top 10 Debtors Panel --}}
        @if($summary['top10']->count() > 0)
        <div class="col-lg-4 no-print">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-trophy text-warning me-2"></i>Top 10 Debtors
                    </h6>
                </div>
                <div class="card-body p-0">
                    @php $maxBalance = $summary['top10']->max('balance'); @endphp
                    @foreach($summary['top10'] as $i => $debtor)
                    <div class="px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $i === 0 ? 'bg-danger' : ($i < 3 ? 'bg-warning text-dark' : 'bg-secondary') }} rounded-pill">
                                    {{ $i + 1 }}
                                </span>
                                <div>
                                    <div class="fw-semibold small">{{ $debtor->name }}</div>
                                    <small class="text-muted">{{ $debtor->phone }}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-danger small">Rs.&nbsp;{{ number_format($debtor->balance, 2) }}</div>
                            </div>
                        </div>
                        <div class="progress mt-1" style="height:4px">
                            <div class="progress-bar bg-danger"
                                 style="width:{{ $maxBalance > 0 ? round($debtor->balance / $maxBalance * 100) : 0 }}%"></div>
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
                    <h5 class="mb-0">Outstanding Balances</h5>
                    <span class="text-muted small">{{ $report->total() }} customer(s)</span>
                </div>
                <div class="card-body p-0">
                    @if($report->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th class="text-center">Sales</th>
                                    <th class="text-end">Total Sales</th>
                                    <th class="text-end">Total Paid</th>
                                    <th class="text-end">Outstanding</th>
                                    <th class="text-end">Credit Limit</th>
                                    <th class="text-end">Available Credit</th>
                                    <th>Last Sale</th>
                                    <th class="text-center no-print">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report as $row)
                                @php
                                    $availCredit = (float)$row->available_credit;
                                    $balance     = (float)$row->outstanding_balance;
                                    $lastSale    = $row->last_sale_date
                                                    ? \Carbon\Carbon::parse($row->last_sale_date)
                                                    : null;
                                    $daysSince   = $lastSale ? $lastSale->diffInDays(now()) : null;
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
                                        @php
                                            $typeLabel = \App\Models\Customer::$types[$row->customer_type] ?? $row->customer_type;
                                            $typeColor = match($row->customer_type) {
                                                'farmer'  => 'primary',
                                                'dealer'  => 'success',
                                                default   => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $typeColor }} badge-sm">{{ $typeLabel }}</span>
                                    </td>
                                    <td>{{ $row->phone ?? '—' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $row->total_sales }}</span>
                                    </td>
                                    <td class="text-end">Rs.&nbsp;{{ number_format($row->total_sales_amount, 2) }}</td>
                                    <td class="text-end text-success">Rs.&nbsp;{{ number_format($row->total_paid, 2) }}</td>
                                    <td class="text-end fw-bold text-danger">
                                        Rs.&nbsp;{{ number_format($balance, 2) }}
                                        @if($row->overdue_30d > 0)
                                            <br><small class="text-danger fw-normal">
                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                                Rs.&nbsp;{{ number_format($row->overdue_30d, 2) }} overdue
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-end text-muted">
                                        {{ $row->credit_limit > 0 ? 'Rs. '.number_format($row->credit_limit, 2) : '—' }}
                                    </td>
                                    <td class="text-end {{ $availCredit < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ $row->credit_limit > 0 ? 'Rs. '.number_format($availCredit, 2) : '—' }}
                                    </td>
                                    <td>
                                        @if($lastSale)
                                            {{ $lastSale->format('d M Y') }}
                                            <br><small class="text-muted">{{ $daysSince }}d ago</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center no-print">
                                        <a href="{{ route('admin.customers.show', $row->id) }}"
                                           class="btn btn-xs btn-outline-primary me-1" title="View Customer">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.reports.customer.ledger', $row->id) }}"
                                           class="btn btn-xs btn-outline-info me-1" title="Ledger">
                                            <i class="bi bi-journal-text"></i>
                                        </a>
                                        <a href="{{ route('admin.reports.customer.payment-history', $row->id) }}"
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
                                    <td class="text-end">Rs.&nbsp;{{ number_format(collect($report->items())->sum('total_sales_amount'), 2) }}</td>
                                    <td class="text-end text-success">Rs.&nbsp;{{ number_format(collect($report->items())->sum('total_paid'), 2) }}</td>
                                    <td class="text-end text-danger">Rs.&nbsp;{{ number_format(collect($report->items())->sum('outstanding_balance'), 2) }}</td>
                                    <td colspan="4"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="card-footer bg-white no-print">
                        {{ $report->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-cash-stack text-muted" style="font-size:3rem"></i>
                        <h5 class="mt-3 text-muted">No Outstanding Balances</h5>
                        <p class="text-muted">No customers with outstanding balances match the current filters.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- row --}}

    <p class="text-muted small mt-3 no-print">
        <i class="bi bi-info-circle me-1"></i>
        Outstanding = sum of <code>due_amount</code> on confirmed, non-cancelled sales.
        Available Credit = Credit Limit − Outstanding Balance.
        Overdue = outstanding on sales older than 30 days.
    </p>

</div>
@endsection

@push('styles')
<style>
.btn-xs { padding:.2rem .45rem; font-size:.75rem; }
.border-3 { border-width:3px !important; }
.badge-sm { font-size:.65rem; }
@media print {
    .no-print { display:none !important; }
    .card { border:none !important; box-shadow:none !important; }
    .table { font-size:10px; }
    body { font-size:11px; }
}
</style>
@endpush
