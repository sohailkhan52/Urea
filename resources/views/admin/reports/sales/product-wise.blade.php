@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Product-Wise Sales Report</h1>
            <p class="text-muted mb-0">Sales analysis by product for the selected period</p>
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
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
            <span class="fw-semibold"><i class="bi bi-funnel me-2"></i>Filters</span>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.sales.product-wise') }}">
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
                        <label class="form-label small fw-semibold">Category</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Min. Quantity</label>
                        <input type="number" name="min_quantity" class="form-control form-control-sm"
                               placeholder="e.g. 10" min="1"
                               value="{{ $filters['min_quantity'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Product name or SKU…"
                               value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Apply</button>
                    <a href="{{ route('admin.reports.sales.product-wise') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle me-1"></i>Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Products Sold</p>
                    <h4 class="mb-0 fw-bold text-primary">{{ number_format($report->total()) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Quantity</p>
                    <h4 class="mb-0 fw-bold text-success">{{ number_format($report->getCollection()->sum('total_quantity')) }}</h4>
                    <small class="text-muted">on this page</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Net Revenue (page)</p>
                    <h5 class="mb-0 fw-bold text-info">Rs.&nbsp;{{ number_format($report->getCollection()->sum('net_amount'), 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-secondary border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Transactions (page)</p>
                    <h4 class="mb-0 fw-bold">{{ number_format($report->getCollection()->sum('sales_count')) }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Product Sales Summary</h5>
            <span class="text-muted small">{{ $report->total() }} product(s)</span>
        </div>
        <div class="card-body p-0">
            @if($report->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">SKU</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th class="text-end">Qty Sold</th>
                            <th class="text-end">Gross Sales</th>
                            <th class="text-end">Avg Unit Price</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Net Sales</th>
                            <th class="text-center">Transactions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report as $item)
                        <tr>
                            <td class="ps-3"><code class="small">{{ $item->sku }}</code></td>
                            <td class="fw-semibold">{{ $item->product_name }}</td>
                            <td>
                                @if($item->category_name)
                                    <span class="badge bg-secondary">{{ $item->category_name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">{{ number_format($item->total_quantity) }}</td>
                            <td class="text-end">Rs.&nbsp;{{ number_format($item->total_amount, 2) }}</td>
                            <td class="text-end text-primary">Rs.&nbsp;{{ number_format($item->average_unit_price, 2) }}</td>
                            <td class="text-end text-danger">Rs.&nbsp;{{ number_format($item->total_discount, 2) }}</td>
                            <td class="text-end fw-semibold text-success">Rs.&nbsp;{{ number_format($item->net_amount, 2) }}</td>
                            <td class="text-center"><span class="badge bg-primary">{{ $item->sales_count }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="3" class="ps-3 text-end">Page Totals:</td>
                            <td class="text-end">{{ number_format($report->getCollection()->sum('total_quantity')) }}</td>
                            <td class="text-end">Rs.&nbsp;{{ number_format($report->getCollection()->sum('total_amount'), 2) }}</td>
                            <td class="text-end">—</td>
                            <td class="text-end text-danger">Rs.&nbsp;{{ number_format($report->getCollection()->sum('total_discount'), 2) }}</td>
                            <td class="text-end">Rs.&nbsp;{{ number_format($report->getCollection()->sum('net_amount'), 2) }}</td>
                            <td class="text-center">{{ $report->getCollection()->sum('sales_count') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer bg-white no-print">
                {{ $report->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-box-seam text-muted" style="font-size:3rem"></i>
                <h5 class="mt-3 text-muted">No Product Sales Found</h5>
                <p class="text-muted">No product sales data found for the selected filters.</p>
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
@media print{* { color: #000000 !important; }.no-print{display:none!important}.card{border:none!important;box-shadow:none!important}.table{font-size:10px} body { background: white; } .text-danger, .text-success, .text-warning, .text-info, .text-primary, .text-secondary { color: #000000 !important; } thead { background-color: white !important; }}
</style>
@endpush
