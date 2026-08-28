@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Current Stock Report</h1>
            <p class="text-muted mb-0">Live inventory levels across warehouses — cost basis: product purchase price</p>
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
                <form method="GET" action="{{ route('admin.reports.inventory.current-stock') }}">
                    <div class="row g-3">

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

                        {{-- Search --}}
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Product name or SKU…"
                                   value="{{ $filters['search'] ?? '' }}">
                        </div>

                        {{-- Stock level toggles --}}
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Stock Level</label>
                            <div class="d-flex gap-2 flex-wrap">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="low_stock"
                                           value="1" id="lowStockCheck"
                                           {{ !empty($filters['low_stock']) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="lowStockCheck">
                                        <span class="badge bg-warning text-dark">Low Stock Only</span>
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="show_zero"
                                           value="0" id="hideZeroCheck"
                                           {{ isset($filters['show_zero']) && $filters['show_zero'] === '0' ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="hideZeroCheck">
                                        Hide Zero Stock
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Apply Filters
                        </button>
                        <a href="{{ route('admin.reports.inventory.current-stock') }}"
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
                    <p class="text-muted small mb-1">Total Products</p>
                    <h4 class="mb-0 fw-bold text-primary">{{ number_format($summary['total_products']) }}</h4>
                    <small class="text-muted">distinct SKUs</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Quantity</p>
                    <h4 class="mb-0 fw-bold text-info">{{ number_format($summary['total_quantity']) }}</h4>
                    <small class="text-muted">units in stock</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Stock Value</p>
                    <h5 class="mb-0 fw-bold text-success">Rs.&nbsp;{{ number_format($summary['total_value'], 2) }}</h5>
                    <small class="text-muted">at purchase price</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Low Stock Items</p>
                    <h4 class="mb-0 fw-bold text-warning">{{ number_format($summary['low_stock_count']) }}</h4>
                    <small class="text-muted">below minimum level</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Out of Stock</p>
                    <h4 class="mb-0 fw-bold text-danger">{{ number_format($summary['out_of_stock_count']) }}</h4>
                    <small class="text-muted">zero quantity</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-secondary border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Showing</p>
                    <h4 class="mb-0 fw-bold">{{ $report->total() }}</h4>
                    <small class="text-muted">inventory rows</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Category Breakdown --}}
    @if($summary['category_breakdown']->count() > 0)
    <div class="card mb-4 no-print">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">Category Summary</h6>
            <button class="btn btn-xs btn-link text-muted p-0" data-bs-toggle="collapse"
                    data-bs-target="#catBreakdown">Toggle</button>
        </div>
        <div class="collapse show" id="catBreakdown">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Category</th>
                                <th class="text-center">Products</th>
                                <th class="text-end">Total Qty</th>
                                <th class="text-end">Total Value</th>
                                <th class="text-end">% of Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($summary['category_breakdown'] as $cat)
                            @php $pct = $summary['total_value'] > 0 ? ($cat->total_value / $summary['total_value'] * 100) : 0; @endphp
                            <tr>
                                <td class="ps-3 fw-semibold">{{ $cat->category_name }}</td>
                                <td class="text-center">{{ $cat->product_count }}</td>
                                <td class="text-end">{{ number_format($cat->total_quantity) }}</td>
                                <td class="text-end">Rs.&nbsp;{{ number_format($cat->total_value, 2) }}</td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                        <div class="progress flex-grow-1" style="height:8px;max-width:80px">
                                            <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                                        </div>
                                        <small>{{ number_format($pct, 1) }}%</small>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Main Data Table --}}
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Stock Levels</h5>
            <span class="text-muted small">{{ $report->total() }} row(s)</span>
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
                            <th>Warehouse</th>
                            <th class="text-end">Current Stock</th>
                            <th class="text-end">Min. Level</th>
                            <th class="text-end">Unit Cost</th>
                            <th class="text-end">Stock Value</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report as $inv)
                        @php
                            $qty     = $inv->quantity;
                            $minLvl  = $inv->product->minimum_stock_level;
                            $cost    = $inv->product->purchase_price;
                            $value   = $qty * $cost;

                            if ($qty == 0) {
                                $statusBadge = 'bg-danger';
                                $statusLabel = 'Out of Stock';
                                $rowClass    = 'table-danger';
                            } elseif ($qty < $minLvl) {
                                $statusBadge = 'bg-warning text-dark';
                                $statusLabel = 'Low Stock';
                                $rowClass    = 'table-warning';
                            } else {
                                $statusBadge = 'bg-success';
                                $statusLabel = 'Normal';
                                $rowClass    = '';
                            }
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td class="ps-3 text-muted">
                                {{ ($report->currentPage() - 1) * $report->perPage() + $loop->iteration }}
                            </td>
                            <td class="fw-semibold">{{ $inv->product->name }}</td>
                            <td><code class="small">{{ $inv->product->sku }}</code></td>
                            <td>
                                @if($inv->product->category)
                                    <span class="badge bg-secondary">{{ $inv->product->category->name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-dark">{{ $inv->warehouse->name }}</span>
                            </td>
                            <td class="text-end fw-bold fs-6">
                                {{ number_format($qty) }}
                                <small class="text-muted fw-normal">{{ $inv->product->weight_unit }}</small>
                            </td>
                            <td class="text-end text-muted">{{ number_format($minLvl) }}</td>
                            <td class="text-end">Rs.&nbsp;{{ number_format($cost, 2) }}</td>
                            <td class="text-end fw-semibold">Rs.&nbsp;{{ number_format($value, 2) }}</td>
                            <td>
                                <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="5" class="ps-3 text-end">Page Totals:</td>
                            <td class="text-end">
                                {{ number_format($report->getCollection()->sum('quantity')) }}
                            </td>
                            <td colspan="2"></td>
                            <td class="text-end">
                                Rs.&nbsp;{{ number_format($report->getCollection()->sum(fn($i) => $i->quantity * $i->product->purchase_price), 2) }}
                            </td>
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
                <i class="bi bi-boxes text-muted" style="font-size:3rem"></i>
                <h5 class="mt-3 text-muted">No Inventory Found</h5>
                <p class="text-muted">No inventory records match the selected filters.</p>
                <a href="{{ route('admin.reports.inventory.current-stock') }}"
                   class="btn btn-outline-primary btn-sm">Reset Filters</a>
            </div>
            @endif
        </div>
    </div>

    <p class="text-muted small mt-3 no-print">
        <i class="bi bi-info-circle me-1"></i>
        Stock value is calculated as <strong>Current Quantity × Product Purchase Price</strong>.
        Low Stock = quantity above zero but below the product's minimum stock level.
    </p>

</div>
@endsection

@push('styles')
<style>
.btn-xs { padding:.2rem .45rem; font-size:.75rem; }
.border-3 { border-width:3px !important; }
code.small { font-size:.8rem; background:#f8f9fa; padding:.1rem .3rem; border-radius:.25rem; }
@media print {
    .no-print { display:none !important; }
    .card { border:none !important; box-shadow:none !important; }
    .table { font-size:10px; }
    body { font-size:11px; }
    .table-danger td { background-color:#f8d7da !important; -webkit-print-color-adjust:exact; }
    .table-warning td { background-color:#fff3cd !important; -webkit-print-color-adjust:exact; }
}
</style>
@endpush
