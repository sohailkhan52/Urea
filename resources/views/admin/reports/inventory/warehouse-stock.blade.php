@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Warehouse Stock Report</h1>
            <p class="text-muted mb-0">
                Side-by-side inventory comparison across
                @if(auth()->user()->isSuperAdmin())
                    all warehouses
                @else
                    your assigned warehouse
                @endif
            </p>
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
                <form method="GET" action="{{ route('admin.reports.inventory.warehouse-stock') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
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
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Product name or SKU…"
                                   value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Apply
                        </button>
                        <a href="{{ route('admin.reports.inventory.warehouse-stock') }}"
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
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Products</p>
                    <h4 class="mb-0 fw-bold text-primary">{{ number_format($summary['total_products']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Quantity</p>
                    <h4 class="mb-0 fw-bold text-info">{{ number_format($summary['total_quantity']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Stock Value</p>
                    <h5 class="mb-0 fw-bold text-success">Rs.&nbsp;{{ number_format($summary['total_value'], 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Out of Stock</p>
                    <h4 class="mb-0 fw-bold text-danger">{{ number_format($summary['out_of_stock_count']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Pivot Table --}}
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Stock by Product
                @foreach($warehouses as $wh)
                    <span class="badge bg-dark ms-1">{{ $wh->name }}</span>
                @endforeach
            </h5>
            <span class="text-muted small">{{ $products->total() }} product(s)</span>
        </div>
        <div class="card-body p-0">
            @if($products->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Category</th>
                            {{-- Dynamic warehouse columns --}}
                            @foreach($warehouses as $wh)
                            <th class="text-end">{{ $wh->name }}</th>
                            @endforeach
                            <th class="text-end">Total Qty</th>
                            <th class="text-end">Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $row)
                        @php
                            $totalQty = $row['total_qty'];
                            $isZero   = $totalQty == 0;
                        @endphp
                        <tr class="{{ $isZero ? 'text-muted' : '' }}">
                            <td class="ps-3 text-muted">
                                {{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}
                            </td>
                            <td class="fw-semibold">{{ $row['product_name'] }}</td>
                            <td><code class="small">{{ $row['sku'] }}</code></td>
                            <td>
                                @if($row['category_name'])
                                    <span class="badge bg-secondary">{{ $row['category_name'] }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            @foreach($warehouses as $wh)
                            @php $qty = $row['quantities'][$wh->id] ?? 0; @endphp
                            <td class="text-end {{ $qty == 0 ? 'text-muted' : '' }}">
                                {{ $qty == 0 ? '—' : number_format($qty) }}
                            </td>
                            @endforeach
                            <td class="text-end fw-bold {{ $isZero ? 'text-danger' : '' }}">
                                {{ number_format($totalQty) }}
                            </td>
                            <td class="text-end fw-semibold">
                                Rs.&nbsp;{{ number_format($row['total_value'], 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="4" class="ps-3 text-end">Column Totals:</td>
                            @foreach($warehouses as $wh)
                            <td class="text-end">
                                {{ number_format(collect($products->items())->sum(fn($r) => $r['quantities'][$wh->id] ?? 0)) }}
                            </td>
                            @endforeach
                            <td class="text-end">
                                {{ number_format(collect($products->items())->sum('total_qty')) }}
                            </td>
                            <td class="text-end">
                                Rs.&nbsp;{{ number_format(collect($products->items())->sum('total_value'), 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer bg-white no-print">
                {{ $products->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-building text-muted" style="font-size:3rem"></i>
                <h5 class="mt-3 text-muted">No Inventory Data</h5>
                <p class="text-muted">No products with inventory records were found.</p>
            </div>
            @endif
        </div>
    </div>

    <p class="text-muted small mt-3 no-print">
        <i class="bi bi-info-circle me-1"></i>
        Only warehouses accessible to your account are shown as columns.
        Total Value = Total Quantity × Product Purchase Price.
        Dashes (—) mean the product has no inventory row in that warehouse.
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
    .table { font-size:9px; }
    body { font-size:10px; }
    th, td { padding:4px 6px !important; }
}
</style>
@endpush
