@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Warehouse-Wise Sales Report</h1>
            <p class="text-muted mb-0">Sales performance comparison across warehouses (Super Admin Only)</p>
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
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.sales.warehouse-wise') }}">
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
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Apply</button>
                    <a href="{{ route('admin.reports.sales.warehouse-wise') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle me-1"></i>Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Overall Total Sales</p>
                    <h4 class="mb-0 fw-bold text-primary">Rs.&nbsp;{{ number_format($grandTotal, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Best Performing Warehouse</p>
                    @if($bestWarehouse)
                        <h5 class="mb-0 fw-bold text-success">{{ $bestWarehouse->name }}</h5>
                        <small class="text-muted">Rs.&nbsp;{{ number_format($bestWarehouse->total_sales_amount, 2) }}</small>
                    @else
                        <p class="text-muted mb-0">No data</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Warehouses</p>
                    <h4 class="mb-0 fw-bold">{{ $warehouses->count() }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Warehouse Sales Performance</h5>
        </div>
        <div class="card-body p-0">
            @if($warehouses->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Warehouse Name</th>
                            <th>Location</th>
                            <th class="text-center">Invoices</th>
                            <th class="text-end">Items Sold</th>
                            <th class="text-end">Total Sales</th>
                            <th class="text-end">Collections</th>
                            <th class="text-end">Outstanding</th>
                            <th class="text-end">Avg Sale</th>
                            <th class="text-center">Contribution</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warehouses as $warehouse)
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $warehouse->name }}</td>
                            <td>{{ $warehouse->location ?? '—' }}</td>
                            <td class="text-center"><span class="badge bg-primary">{{ $warehouse->total_invoices }}</span></td>
                            <td class="text-end">{{ number_format($warehouse->total_items_sold) }}</td>
                            <td class="text-end fw-semibold">Rs.&nbsp;{{ number_format($warehouse->total_sales_amount, 2) }}</td>
                            <td class="text-end text-success">Rs.&nbsp;{{ number_format($warehouse->total_collections, 2) }}</td>
                            <td class="text-end {{ $warehouse->outstanding_amount > 0 ? 'text-danger' : 'text-muted' }}">Rs.&nbsp;{{ number_format($warehouse->outstanding_amount, 2) }}</td>
                            <td class="text-end">Rs.&nbsp;{{ number_format($warehouse->average_sale_value, 2) }}</td>
                            <td class="text-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:12px">
                                        <div class="progress-bar bg-primary" style="width:{{ $warehouse->contribution_percentage }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $warehouse->contribution_percentage }}%</small>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="2" class="ps-3 text-end">Grand Totals:</td>
                            <td class="text-center">{{ $warehouses->sum('total_invoices') }}</td>
                            <td class="text-end">{{ number_format($warehouses->sum('total_items_sold')) }}</td>
                            <td class="text-end">Rs.&nbsp;{{ number_format($warehouses->sum('total_sales_amount'), 2) }}</td>
                            <td class="text-end">Rs.&nbsp;{{ number_format($warehouses->sum('total_collections'), 2) }}</td>
                            <td class="text-end">Rs.&nbsp;{{ number_format($warehouses->sum('outstanding_amount'), 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-building text-muted" style="font-size:3rem"></i>
                <h5 class="mt-3 text-muted">No Warehouse Data</h5>
                <p class="text-muted">No warehouse sales data found for the selected period.</p>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
@media print{* { color: #000000 !important; }.no-print{display:none!important}.card{border:none!important;box-shadow:none!important}.table{font-size:10px} body { background: white; } .text-danger, .text-success, .text-warning, .text-info, .text-primary, .text-secondary { color: #000000 !important; } thead { background-color: white !important; }}
</style>
@endpush
