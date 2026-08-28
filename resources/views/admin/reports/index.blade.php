@extends('layouts.admin')

@section('content')
@php
    $r   = $data['summary'];
    $gmc = $r['gross_margin'] >= 30 ? 'success' : ($r['gross_margin'] >= 15 ? 'warning' : 'danger');
    $npc = $r['net_profit']   >= 0  ? 'success' : 'danger';
@endphp

<div class="container-fluid py-4">

    {{-- ── Page Header ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Reports Dashboard</h1>
            <p class="text-muted mb-0">
                {{ \Carbon\Carbon::parse($filters['date_from'])->format('d M Y') }}
                &nbsp;–&nbsp;
                {{ \Carbon\Carbon::parse($filters['date_to'])->format('d M Y') }}
                @if(!empty($filters['warehouse_id']))
                    &nbsp;·&nbsp;
                    <span class="badge bg-secondary">
                        {{ optional(\App\Models\Warehouse::find($filters['warehouse_id']))->name }}
                    </span>
                @endif
            </p>
        </div>
        <div class="no-print">
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
                <form method="GET" action="{{ route('admin.reports.index') }}">
                    <div class="row g-3 align-items-end">

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Date From</label>
                            <input type="date" name="date_from" id="date_from"
                                   class="form-control form-control-sm"
                                   value="{{ $filters['date_from'] }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Date To</label>
                            <input type="date" name="date_to" id="date_to"
                                   class="form-control form-control-sm"
                                   value="{{ $filters['date_to'] }}">
                        </div>

                        {{-- Quick presets --}}
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">Quick Period</label>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('today')">Today</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('yesterday')">Yesterday</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_week')">This Week</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('last_week')">Last Week</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_month')">This Month</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('last_month')">Last Month</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_quarter')">This Qtr</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('last_quarter')">Last Qtr</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_year')">This Year</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('last_year')">Last Year</button>
                            </div>
                        </div>

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

                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Apply
                        </button>
                        <a href="{{ route('admin.reports.index') }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Summary Cards (7 cards) ── --}}
    <div class="row g-3 mb-4">

        {{-- Total Sales --}}
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.reports.sales.daily', request()->only(['date_from','date_to','warehouse_id'])) }}"
               class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 border-start border-primary border-3 card-hover">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small mb-1">Total Sales</p>
                                <h5 class="mb-0 fw-bold text-primary">Rs.&nbsp;{{ number_format($r['total_sales'], 2) }}</h5>
                            </div>
                            <i class="bi bi-cart-check text-primary opacity-50 fs-3"></i>
                        </div>
                        <small class="text-muted">Confirmed sales in period</small>
                    </div>
                </div>
            </a>
        </div>

        {{-- Total Purchases --}}
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.reports.purchase.daily', request()->only(['date_from','date_to','warehouse_id'])) }}"
               class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 border-start border-warning border-3 card-hover">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small mb-1">Total Purchases</p>
                                <h5 class="mb-0 fw-bold text-warning">Rs.&nbsp;{{ number_format($r['total_purchases'], 2) }}</h5>
                            </div>
                            <i class="bi bi-cart-plus text-warning opacity-50 fs-3"></i>
                        </div>
                        <small class="text-muted">Confirmed POs in period</small>
                    </div>
                </div>
            </a>
        </div>

        {{-- Total Receivables --}}
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.reports.customer.outstanding') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 border-start border-danger border-3 card-hover">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small mb-1">Total Receivables</p>
                                <h5 class="mb-0 fw-bold text-danger">Rs.&nbsp;{{ number_format($r['total_receivables'], 2) }}</h5>
                            </div>
                            <i class="bi bi-person-check text-danger opacity-50 fs-3"></i>
                        </div>
                        <small class="text-muted">All-time outstanding</small>
                    </div>
                </div>
            </a>
        </div>

        {{-- Total Payables --}}
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.reports.supplier.outstanding') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 border-start border-orange border-3 card-hover">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small mb-1">Total Payables</p>
                                <h5 class="mb-0 fw-bold" style="color:#e65100">Rs.&nbsp;{{ number_format($r['total_payables'], 2) }}</h5>
                            </div>
                            <i class="bi bi-truck opacity-50 fs-3" style="color:#e65100"></i>
                        </div>
                        <small class="text-muted">All-time outstanding</small>
                    </div>
                </div>
            </a>
        </div>

        {{-- Inventory Value --}}
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.reports.inventory.current-stock') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 border-start border-info border-3 card-hover">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small mb-1">Inventory Value</p>
                                <h5 class="mb-0 fw-bold text-info">Rs.&nbsp;{{ number_format($r['inventory_value'], 2) }}</h5>
                            </div>
                            <i class="bi bi-boxes text-info opacity-50 fs-3"></i>
                        </div>
                        <small class="text-muted">Live stock at cost</small>
                    </div>
                </div>
            </a>
        </div>

        {{-- Gross Profit --}}
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.reports.profit-loss', request()->only(['date_from','date_to','warehouse_id'])) }}"
               class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 border-start border-{{ $gmc }} border-3 card-hover">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small mb-1">Gross Profit</p>
                                <h5 class="mb-0 fw-bold text-{{ $gmc }}">Rs.&nbsp;{{ number_format($r['gross_profit'], 2) }}</h5>
                            </div>
                            <i class="bi bi-graph-up text-{{ $gmc }} opacity-50 fs-3"></i>
                        </div>
                        <small class="text-{{ $gmc }}">{{ $r['gross_margin'] }}% margin</small>
                    </div>
                </div>
            </a>
        </div>

        {{-- Net Profit --}}
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.reports.profit-loss', request()->only(['date_from','date_to','warehouse_id'])) }}"
               class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 border-start border-{{ $npc }} border-3 card-hover">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small mb-1">Net Profit</p>
                                <h5 class="mb-0 fw-bold text-{{ $npc }}">Rs.&nbsp;{{ number_format($r['net_profit'], 2) }}</h5>
                            </div>
                            <i class="bi bi-{{ $r['net_profit'] >= 0 ? 'trophy' : 'exclamation-triangle' }} text-{{ $npc }} opacity-50 fs-3"></i>
                        </div>
                        <small class="text-muted">Gross profit − expenses</small>
                    </div>
                </div>
            </a>
        </div>

    </div>

    {{-- ── Charts Row 1: Trend + Profit ── --}}
    <div class="row g-4 mb-4">

        {{-- Sales & Purchase Trend --}}
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Sales &amp; Purchase Trend</h6>
                    <small class="text-muted">{{ count($data['trendData']['labels']) }} data points</small>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="90"></canvas>
                </div>
            </div>
        </div>

        {{-- Profit Gauge --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold">Profit Overview</h6>
                </div>
                <div class="card-body">
                    <canvas id="profitChart" height="160"></canvas>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Gross Margin</small>
                            <small class="fw-semibold text-{{ $gmc }}">{{ $r['gross_margin'] }}%</small>
                        </div>
                        <div class="progress mb-3" style="height:10px">
                            <div class="progress-bar bg-{{ $gmc }}"
                                 style="width:{{ min(max($r['gross_margin'],0),100) }}%"></div>
                        </div>
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="text-muted small ps-0">Net Sales</td>
                                <td class="text-end fw-semibold small">Rs.&nbsp;{{ number_format($r['net_sales'], 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small ps-0">COGS</td>
                                <td class="text-end text-warning fw-semibold small">Rs.&nbsp;{{ number_format($r['net_sales'] - $r['gross_profit'], 2) }}</td>
                            </tr>
                            <tr class="border-top">
                                <td class="text-muted small ps-0 fw-bold">Gross Profit</td>
                                <td class="text-end fw-bold small text-{{ $gmc }}">Rs.&nbsp;{{ number_format($r['gross_profit'], 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Charts Row 2: Top Products + Top Customers ── --}}
    <div class="row g-4 mb-4">

        {{-- Top 10 Products --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-box-seam me-2 text-primary"></i>Top Selling Products</h6>
                    <a href="{{ route('admin.reports.sales.product-wise', request()->only(['date_from','date_to','warehouse_id'])) }}"
                       class="btn btn-xs btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($data['topProducts']->count() > 0)
                    @php $maxRev = $data['topProducts']->max('total_revenue'); @endphp
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Product</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['topProducts'] as $i => $prod)
                                <tr>
                                    <td class="ps-3 text-muted">{{ $i+1 }}</td>
                                    <td>
                                        <div class="fw-semibold small">{{ $prod->product_name }}</div>
                                        <div style="height:4px;border-radius:2px;background:#e9ecef;margin-top:3px">
                                            <div style="height:4px;border-radius:2px;background:#0d6efd;width:{{ $maxRev>0?round($prod->total_revenue/$maxRev*100):0 }}%"></div>
                                        </div>
                                    </td>
                                    <td class="text-end small">{{ number_format($prod->total_qty, 0) }}</td>
                                    <td class="text-end small fw-semibold">Rs.&nbsp;{{ number_format($prod->total_revenue, 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-box-seam fs-2 opacity-25"></i>
                        <p class="small mt-2">No sales in this period</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top 10 Customers --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-people me-2 text-success"></i>Top Customers</h6>
                    <a href="{{ route('admin.reports.sales.customer-wise', request()->only(['date_from','date_to','warehouse_id'])) }}"
                       class="btn btn-xs btn-outline-success">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($data['topCustomers']->count() > 0)
                    @php $maxCust = $data['topCustomers']->max('total_amount'); @endphp
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Customer</th>
                                    <th class="text-center">Inv.</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['topCustomers'] as $i => $cust)
                                <tr>
                                    <td class="ps-3 text-muted">{{ $i+1 }}</td>
                                    <td>
                                        <div class="fw-semibold small">{{ $cust->customer_name }}</div>
                                        <div style="height:4px;border-radius:2px;background:#e9ecef;margin-top:3px">
                                            <div style="height:4px;border-radius:2px;background:#198754;width:{{ $maxCust>0?round($cust->total_amount/$maxCust*100):0 }}%"></div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $cust->total_invoices }}</span>
                                    </td>
                                    <td class="text-end small fw-semibold">Rs.&nbsp;{{ number_format($cust->total_amount, 0) }}</td>
                                    <td class="text-end small {{ $cust->outstanding > 0 ? 'text-danger' : 'text-muted' }}">
                                        Rs.&nbsp;{{ number_format($cust->outstanding, 0) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-people fs-2 opacity-25"></i>
                        <p class="small mt-2">No sales in this period</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- ── Charts Row 3: Top Suppliers + Low Stock ── --}}
    <div class="row g-4 mb-4">

        {{-- Top 10 Suppliers --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-truck me-2 text-warning"></i>Top Suppliers</h6>
                    <a href="{{ route('admin.reports.purchase.supplier-wise', request()->only(['date_from','date_to','warehouse_id'])) }}"
                       class="btn btn-xs btn-outline-warning">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($data['topSuppliers']->count() > 0)
                    @php $maxSup = $data['topSuppliers']->max('total_amount'); @endphp
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Supplier</th>
                                    <th class="text-center">POs</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Payable</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['topSuppliers'] as $i => $sup)
                                <tr>
                                    <td class="ps-3 text-muted">{{ $i+1 }}</td>
                                    <td>
                                        <div class="fw-semibold small">{{ $sup->supplier_name }}</div>
                                        <div style="height:4px;border-radius:2px;background:#e9ecef;margin-top:3px">
                                            <div style="height:4px;border-radius:2px;background:#ffc107;width:{{ $maxSup>0?round($sup->total_amount/$maxSup*100):0 }}%"></div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark">{{ $sup->total_pos }}</span>
                                    </td>
                                    <td class="text-end small fw-semibold">Rs.&nbsp;{{ number_format($sup->total_amount, 0) }}</td>
                                    <td class="text-end small {{ $sup->outstanding > 0 ? 'text-danger' : 'text-muted' }}">
                                        Rs.&nbsp;{{ number_format($sup->outstanding, 0) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-truck fs-2 opacity-25"></i>
                        <p class="small mt-2">No purchases in this period</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Low Stock Alert --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-exclamation-triangle me-2 text-danger"></i>
                        Low Stock Alert
                        @if($data['lowStock']->count() > 0)
                            <span class="badge bg-danger ms-1">{{ $data['lowStock']->count() }}</span>
                        @endif
                    </h6>
                    <a href="{{ route('admin.reports.inventory.current-stock', array_merge(request()->only(['warehouse_id']), ['low_stock'=>1])) }}"
                       class="btn btn-xs btn-outline-danger">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($data['lowStock']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Product</th>
                                    <th>Warehouse</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Min</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['lowStock'] as $item)
                                <tr class="{{ $item->quantity == 0 ? 'table-danger' : 'table-warning' }}">
                                    <td class="ps-3">
                                        <div class="fw-semibold small">{{ $item->product_name }}</div>
                                        <small class="text-muted">{{ $item->sku }}</small>
                                    </td>
                                    <td><span class="badge bg-secondary">{{ $item->warehouse_name }}</span></td>
                                    <td class="text-end fw-bold {{ $item->quantity == 0 ? 'text-danger' : 'text-warning' }}">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="text-end text-muted small">{{ $item->minimum_stock_level }}</td>
                                    <td>
                                        @if($item->quantity == 0)
                                            <span class="badge bg-danger">Out</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Low</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-success">
                        <i class="bi bi-check-circle fs-2 opacity-50"></i>
                        <p class="small mt-2">All products are adequately stocked</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- ── Quick Links ── --}}
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0 fw-semibold"><i class="bi bi-grid me-2"></i>Quick Links</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @php
                $links = [
                    ['route' => 'admin.reports.sales.index',         'icon' => 'bi-graph-up',              'label' => 'Sales Reports',     'color' => 'primary'],
                    ['route' => 'admin.reports.purchase.index',      'icon' => 'bi-cart-plus',             'label' => 'Purchase Reports',  'color' => 'warning'],
                    ['route' => 'admin.reports.invoices',            'icon' => 'bi-receipt',               'label' => 'Invoice Report',    'color' => 'info'],
                    ['route' => 'admin.reports.inventory.index',     'icon' => 'bi-boxes',                 'label' => 'Inventory Reports', 'color' => 'success'],
                    ['route' => 'admin.reports.customer.index',      'icon' => 'bi-person-lines-fill',     'label' => 'Customer Reports',  'color' => 'danger'],
                    ['route' => 'admin.reports.supplier.index',      'icon' => 'bi-truck',                 'label' => 'Supplier Reports',  'color' => 'secondary'],
                    ['route' => 'admin.reports.profit-loss',         'icon' => 'bi-currency-dollar',       'label' => 'Profit & Loss',     'color' => 'dark'],
                ];
                @endphp
                @foreach($links as $link)
                <div class="col-6 col-md-3 col-lg">
                    <a href="{{ route($link['route']) }}"
                       class="d-flex flex-column align-items-center justify-content-center p-3 rounded border text-{{ $link['color'] }} text-decoration-none quick-link-card">
                        <i class="bi {{ $link['icon'] }} fs-2 mb-2"></i>
                        <span class="small fw-semibold text-center">{{ $link['label'] }}</span>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
.btn-xs   { padding:.2rem .45rem; font-size:.75rem; }
.border-3 { border-width:3px !important; }

.card-hover {
    transition: transform .15s, box-shadow .15s;
    cursor: pointer;
}
.card-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important;
}

.quick-link-card {
    transition: background .15s, transform .15s;
    background: #fff;
}
.quick-link-card:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
}

@media print {
    * { color: #000000 !important; }
    .no-print { display:none !important; }
    .card { border:none !important; box-shadow:none !important; page-break-inside:avoid; }
}
    canvas { display:none !important; }
    body { font-size:11px; }
    .table { font-size:10px; }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Date presets ──────────────────────────────────────────────────────────────
function setRange(preset) {
    const df = document.getElementById('date_from');
    const dt = document.getElementById('date_to');
    const today = new Date();
    const fmt = d => d.toISOString().slice(0,10);
    const q = m => Math.floor(m/3);
    switch(preset) {
        case 'today':     df.value = dt.value = fmt(today); break;
        case 'yesterday': { const y=new Date(today); y.setDate(y.getDate()-1); df.value=dt.value=fmt(y); break; }
        case 'this_week': {
            const mon=new Date(today); mon.setDate(today.getDate()-(today.getDay()===0?6:today.getDay()-1));
            df.value=fmt(mon); dt.value=fmt(today); break;
        }
        case 'last_week': {
            const mon=new Date(today); mon.setDate(today.getDate()-(today.getDay()===0?6:today.getDay()-1)-7);
            const sun=new Date(mon); sun.setDate(mon.getDate()+6);
            df.value=fmt(mon); dt.value=fmt(sun); break;
        }
        case 'this_month': df.value=fmt(new Date(today.getFullYear(),today.getMonth(),1)); dt.value=fmt(today); break;
        case 'last_month':
            df.value=fmt(new Date(today.getFullYear(),today.getMonth()-1,1));
            dt.value=fmt(new Date(today.getFullYear(),today.getMonth(),0)); break;
        case 'this_quarter': {
            const qm=q(today.getMonth())*3;
            df.value=fmt(new Date(today.getFullYear(),qm,1)); dt.value=fmt(today); break;
        }
        case 'last_quarter': {
            const lq=q(today.getMonth())-1, lqY=lq<0?today.getFullYear()-1:today.getFullYear(), lqM=lq<0?9:lq*3;
            df.value=fmt(new Date(lqY,lqM,1)); dt.value=fmt(new Date(lqY,lqM+3,0)); break;
        }
        case 'this_year': df.value=fmt(new Date(today.getFullYear(),0,1)); dt.value=fmt(today); break;
        case 'last_year':
            df.value=fmt(new Date(today.getFullYear()-1,0,1));
            dt.value=fmt(new Date(today.getFullYear()-1,11,31)); break;
    }
}

// ── Trend chart ───────────────────────────────────────────────────────────────
const trendData = @json($data['trendData']);
const trendCtx = document.getElementById('trendChart');
if (trendCtx && trendData.labels.length > 0) {
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendData.labels,
            datasets: [
                {
                    label: 'Sales',
                    data: trendData.sales,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13,110,253,0.06)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: trendData.labels.length > 30 ? 0 : 3,
                    borderWidth: 2,
                },
                {
                    label: 'Purchases',
                    data: trendData.purchases,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255,193,7,0.06)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: trendData.labels.length > 30 ? 0 : 3,
                    borderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: { label: c => ' Rs. ' + c.parsed.y.toLocaleString(undefined,{minimumFractionDigits:2}) }
                }
            },
            scales: {
                y: { ticks: { callback: v => 'Rs. ' + v.toLocaleString() } }
            }
        },
    });
}

// ── Profit doughnut ───────────────────────────────────────────────────────────
const profitCtx = document.getElementById('profitChart');
if (profitCtx) {
    const grossProfit = {{ max($r['gross_profit'], 0) }};
    const cogs        = {{ max($r['net_sales'] - $r['gross_profit'], 0) }};
    const returns_d   = {{ max($r['total_sales'] - $r['net_sales'], 0) }};

    new Chart(profitCtx, {
        type: 'doughnut',
        data: {
            labels: ['Gross Profit', 'COGS', 'Returns & Discounts'],
            datasets: [{
                data: [grossProfit, cogs, returns_d],
                backgroundColor: ['#198754','#ffc107','#6c757d'],
                borderWidth: 2,
            }]
        },
        options: {
            responsive: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 } } },
                tooltip: {
                    callbacks: { label: c => ' Rs. ' + c.parsed.toLocaleString(undefined,{minimumFractionDigits:2}) }
                }
            }
        }
    });
}
</script>
@endpush
