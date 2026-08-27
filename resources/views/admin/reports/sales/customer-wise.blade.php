@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Customer-Wise Sales Report</h1>
            <p class="text-muted mb-0">Sales analysis by customer for the selected period</p>
        </div>
        <div class="d-flex gap-2 no-print">
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print
            </button>
            <a href="{{ route('admin.reports.sales.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4 no-print">
        <div class="card-header bg-light py-2">
            <span class="fw-semibold"><i class="bi bi-funnel me-2"></i>Filters</span>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.sales.customer-wise') }}">
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
                    @if(auth()->user()->isSuperAdmin())
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Warehouse</label>
                        <select name="warehouse_id" class="form-select form-select-sm">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ ($filters['warehouse_id'] ?? '') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Customer</label>
                        <select name="customer_id" class="form-select form-select-sm">
                            <option value="">All Customers</option>
                            @foreach($customers as $cust)
                                <option value="{{ $cust->id }}" {{ ($filters['customer_id'] ?? '') == $cust->id ? 'selected' : '' }}>{{ $cust->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Min Sales Amount</label>
                        <input type="number" name="min_sales" class="form-control form-control-sm"
                               placeholder="e.g. 10000" min="0"
                               value="{{ $filters['min_sales'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Name or phone…"
                               value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Apply</button>
                    <a href="{{ route('admin.reports.sales.customer-wise') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle me-1"></i>Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Customer Sales Summary</h5>
            <span class="text-muted small">{{ $report->total() }} customer(s)</span>
        </div>
        <div class="card-body p-0">
            @if($report->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Customer Name</th>
                            <th>Phone</th>
                            <th class="text-center">Total Invoices</th>
                            <th class="text-end">Total Qty</th>
                            <th class="text-end">Total Sales</th>
                            <th class="text-end">Total Paid</th>
                            <th class="text-end">Outstanding</th>
                            <th>Last Sale</th>
                            <th class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report as $customer)
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $customer->name }}</td>
                            <td>{{ $customer->phone ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $customer->total_sales }}</span>
                            </td>
                            <td class="text-end">{{ number_format($customer->total_quantity) }}</td>
                            <td class="text-end fw-semibold">Rs.&nbsp;{{ number_format($customer->total_amount, 2) }}</td>
                            <td class="text-end text-success">Rs.&nbsp;{{ number_format($customer->total_paid, 2) }}</td>
                            <td class="text-end {{ $customer->total_due > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">
                                Rs.&nbsp;{{ number_format($customer->total_due, 2) }}
                            </td>
                            <td>
                                @if($customer->last_sale_date)
                                    {{ \Carbon\Carbon::parse($customer->last_sale_date)->format('d M Y') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center no-print">
                                <a href="{{ route('admin.customers.show', $customer->id) }}"
                                   class="btn btn-xs btn-outline-primary me-1" title="View Customer">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="2" class="ps-3 text-end">Page Totals:</td>
                            <td class="text-center">{{ $report->getCollection()->sum('total_sales') }}</td>
                            <td class="text-end">{{ number_format($report->getCollection()->sum('total_quantity')) }}</td>
                            <td class="text-end">Rs.&nbsp;{{ number_format($report->getCollection()->sum('total_amount'), 2) }}</td>
                            <td class="text-end text-success">Rs.&nbsp;{{ number_format($report->getCollection()->sum('total_paid'), 2) }}</td>
                            <td class="text-end text-danger">Rs.&nbsp;{{ number_format($report->getCollection()->sum('total_due'), 2) }}</td>
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
                <i class="bi bi-people text-muted" style="font-size:3rem"></i>
                <h5 class="mt-3 text-muted">No Customer Sales Found</h5>
                <p class="text-muted">No customer sales data found for the selected filters.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.btn-xs{padding:.2rem .45rem;font-size:.75rem}
.border-3{border-width:3px!important}
@media print{.no-print{display:none!important}.card{border:none!important;box-shadow:none!important}.table{font-size:10px}}
</style>
@endpush
