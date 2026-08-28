@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Product-Wise Purchase Report</h1>
            <p class="text-muted mb-0">Purchase analysis grouped by product — confirmed purchases only</p>
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
                <form method="GET" action="{{ route('admin.reports.purchase.product-wise') }}">
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

                        {{-- Category --}}
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Category</label>
                            <select name="category_id" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Product --}}
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Product</label>
                            <select name="product_id" class="form-select form-select-sm">
                                <option value="">All Products</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}"
                                        {{ ($filters['product_id'] ?? '') == $prod->id ? 'selected' : '' }}>
                                        {{ $prod->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Minimum Quantity --}}
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Min Quantity</label>
                            <input type="number" name="min_quantity" class="form-control form-control-sm"
                                   placeholder="e.g. 50" min="0" step="0.01"
                                   value="{{ $filters['min_quantity'] ?? '' }}">
                        </div>

                        {{-- Search --}}
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Product name or SKU…"
                                   value="{{ $filters['search'] ?? '' }}">
                        </div>

                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Apply Filters
                        </button>
                        <a href="{{ route('admin.reports.purchase.product-wise') }}"
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
                    <p class="text-muted small mb-1">Total Products</p>
                    <h4 class="mb-0 fw-bold">{{ number_format($summary['total_products']) }}</h4>
                    <small class="text-muted">distinct SKUs purchased</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Quantity</p>
                    <h4 class="mb-0 text-primary fw-bold">{{ number_format($summary['total_quantity'], 0) }}</h4>
                    <small class="text-muted">units purchased</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Cost</p>
                    <h4 class="mb-0 text-danger fw-bold">Rs.&nbsp;{{ number_format($summary['total_cost'], 2) }}</h4>
                    <small class="text-muted">gross purchase value</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Most Purchased (Top 5)</p>
                    @if(count($summary['top_products']) > 0)
                        @foreach($summary['top_products'] as $tp)
                            @php $prod = \App\Models\Product::find($tp->product_id) @endphp
                            <div class="d-flex justify-content-between">
                                <small class="text-truncate" style="max-width:70%">{{ $prod?->name ?? 'Unknown' }}</small>
                                <small class="text-muted">{{ number_format($tp->qty, 0) }}</small>
                            </div>
                        @endforeach
                    @else
                        <small class="text-muted">No data</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Data Table ── --}}
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Product Purchase Details</h5>
            <span class="text-muted small">{{ $report->total() }} product(s)</span>
        </div>
        <div class="card-body p-0">
            @if($report->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th class="text-end">Qty Purchased</th>
                            <th class="text-end">Gross Amount</th>
                            <th class="text-end">Avg Unit Cost</th>
                            <th class="text-end">Qty Returned</th>
                            <th class="text-end">Returned Amount</th>
                            <th class="text-end">Net Amount</th>
                            <th class="text-center">Transactions</th>
                            <th>Last Purchase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report as $row)
                        @php
                            $isTopProduct = $summary['top_products']->pluck('product_id')->contains($row->id);
                        @endphp
                        <tr class="{{ $isTopProduct ? 'table-info' : '' }}">
                            <td class="ps-3 text-muted">
                                {{ ($report->currentPage() - 1) * $report->perPage() + $loop->iteration }}
                                @if($isTopProduct)
                                    <span class="badge bg-info text-dark ms-1" title="Top purchased product">★</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $row->product_name }}</td>
                            <td><code class="small">{{ $row->sku }}</code></td>
                            <td>
                                @if($row->category_name)
                                    <span class="badge bg-secondary">{{ $row->category_name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end fw-semibold">{{ number_format($row->total_quantity, 2) }}</td>
                            <td class="text-end">Rs.&nbsp;{{ number_format($row->gross_amount, 2) }}</td>
                            <td class="text-end text-primary">Rs.&nbsp;{{ number_format($row->avg_unit_cost, 4) }}</td>
                            <td class="text-end {{ $row->returned_quantity > 0 ? 'text-warning' : 'text-muted' }}">
                                {{ $row->returned_quantity > 0 ? number_format($row->returned_quantity, 2) : '—' }}
                            </td>
                            <td class="text-end {{ $row->returned_amount > 0 ? 'text-warning' : 'text-muted' }}">
                                {{ $row->returned_amount > 0 ? 'Rs. '.number_format($row->returned_amount, 2) : '—' }}
                            </td>
                            <td class="text-end fw-semibold text-success">
                                Rs.&nbsp;{{ number_format($row->net_amount, 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $row->purchase_count }}</span>
                            </td>
                            <td>
                                @if($row->last_purchase_date)
                                    {{ \Carbon\Carbon::parse($row->last_purchase_date)->format('d M Y') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="4" class="ps-3 text-end">Page Totals:</td>
                            <td class="text-end">{{ number_format($report->getCollection()->sum('total_quantity'), 2) }}</td>
                            <td class="text-end">Rs.&nbsp;{{ number_format($report->getCollection()->sum('gross_amount'), 2) }}</td>
                            <td class="text-end">—</td>
                            <td class="text-end text-warning">{{ number_format($report->getCollection()->sum('returned_quantity'), 2) }}</td>
                            <td class="text-end text-warning">Rs.&nbsp;{{ number_format($report->getCollection()->sum('returned_amount'), 2) }}</td>
                            <td class="text-end text-success">Rs.&nbsp;{{ number_format($report->getCollection()->sum('net_amount'), 2) }}</td>
                            <td class="text-center">{{ $report->getCollection()->sum('purchase_count') }}</td>
                            <td></td>
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
                <h5 class="mt-3 text-muted">No Product Purchase Data Found</h5>
                <p class="text-muted">No confirmed purchases match the selected filters.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Returns note --}}
    <p class="text-muted small mt-3 no-print">
        <i class="bi bi-info-circle me-1"></i>
        <strong>Returns</strong> column shows quantities/amounts from <em>confirmed</em> purchase returns
        within the selected date range. Net Amount = Gross Amount − Returned Amount.
        Only <em>confirmed</em> purchases are included in this report.
    </p>

</div>
@endsection

@push('styles')
<style>
.btn-xs { padding:.2rem .45rem; font-size:.75rem; }
code.small { font-size:.8rem; background:#f8f9fa; padding:.1rem .3rem; border-radius:.25rem; }
@media print {
    * { color: #000000 !important; }
    .no-print { display:none !important; }
    .card { border:none !important; box-shadow:none !important; }
}
    .table { font-size:10px; }
    body { font-size:11px; }
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
