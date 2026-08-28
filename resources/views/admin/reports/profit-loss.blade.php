@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Page Header ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Profit &amp; Loss Report</h1>
            <p class="text-muted mb-0">
                {{ \Carbon\Carbon::parse($report['date_from'])->format('d M Y') }}
                &nbsp;–&nbsp;
                {{ \Carbon\Carbon::parse($report['date_to'])->format('d M Y') }}
                @if(!empty($filters['warehouse_id']))
                    &nbsp;·&nbsp;
                    <span class="badge bg-secondary">
                        {{ optional(\App\Models\Warehouse::find($filters['warehouse_id']))->name }}
                    </span>
                @endif
            </p>
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
                <form method="GET" action="{{ route('admin.reports.profit-loss') }}">
                    <div class="row g-3 align-items-end">

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Date From</label>
                            <input type="date" name="date_from" class="form-control form-control-sm"
                                   value="{{ $filters['date_from'] }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Date To</label>
                            <input type="date" name="date_to" class="form-control form-control-sm"
                                   value="{{ $filters['date_to'] }}">
                        </div>

                        {{-- Quick presets --}}
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Quick Period</label>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_month')">This Month</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('last_month')">Last Month</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('this_quarter')">This Quarter</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="setRange('last_quarter')">Last Quarter</button>
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

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Compare</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" name="compare_mode"
                                       value="1" id="compareMode"
                                       {{ !empty($filters['compare_mode']) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="compareMode">
                                    Previous period
                                </label>
                            </div>
                        </div>

                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Apply
                        </button>
                        <a href="{{ route('admin.reports.profit-loss') }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Summary Cards ── --}}
    @php
        $r = $report;
        $c = $r['comparison'];
        $netProfit = $r['net_profit'];
        $grossMarginColor = $r['gross_margin'] >= 30 ? 'success' : ($r['gross_margin'] >= 15 ? 'warning' : 'danger');
        $netMarginColor   = $r['net_margin']   >= 20 ? 'success' : ($r['net_margin']   >= 10 ? 'warning' : 'danger');

        // Growth helper: positive = improvement
        $growth = fn($curr, $prev) => $prev != 0 ? round((($curr - $prev) / abs($prev)) * 100, 1) : null;
    @endphp

    <div class="row g-3 mb-4">

        {{-- Net Sales --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Net Sales Revenue</p>
                    <h4 class="mb-0 fw-bold text-primary">Rs.&nbsp;{{ number_format($r['net_sales'], 2) }}</h4>
                    @if($c)
                    @php $g = $growth($r['net_sales'], $c['net_sales']); @endphp
                    <small class="{{ $g === null ? 'text-muted' : ($g >= 0 ? 'text-success' : 'text-danger') }}">
                        @if($g !== null)
                            <i class="bi bi-arrow-{{ $g >= 0 ? 'up' : 'down' }}-short"></i>{{ abs($g) }}% vs prev
                        @else —
                        @endif
                    </small>
                    @endif
                </div>
            </div>
        </div>

        {{-- COGS --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Cost of Goods Sold</p>
                    <h4 class="mb-0 fw-bold text-warning">Rs.&nbsp;{{ number_format($r['cogs'], 2) }}</h4>
                    @if($c)
                    @php $g = $growth($r['cogs'], $c['cogs']); $gpct = $g !== null ? -$g : null; @endphp
                    <small class="{{ $gpct === null ? 'text-muted' : ($gpct >= 0 ? 'text-success' : 'text-danger') }}">
                        @if($gpct !== null)
                            <i class="bi bi-arrow-{{ $g >= 0 ? 'up' : 'down' }}-short"></i>{{ abs($g) }}% vs prev
                        @else —
                        @endif
                    </small>
                    @endif
                </div>
            </div>
        </div>

        {{-- Gross Profit --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-{{ $grossMarginColor }} border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Gross Profit</p>
                    <h4 class="mb-0 fw-bold text-{{ $grossMarginColor }}">Rs.&nbsp;{{ number_format($r['gross_profit'], 2) }}</h4>
                    <small class="text-{{ $grossMarginColor }}">{{ $r['gross_margin'] }}% margin</small>
                    @if($c)
                    @php $g = $growth($r['gross_profit'], $c['gross_profit']); @endphp
                    <br><small class="{{ $g === null ? 'text-muted' : ($g >= 0 ? 'text-success' : 'text-danger') }}">
                        @if($g !== null)
                            <i class="bi bi-arrow-{{ $g >= 0 ? 'up' : 'down' }}-short"></i>{{ abs($g) }}%
                        @endif
                    </small>
                    @endif
                </div>
            </div>
        </div>

        {{-- Net Profit --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-{{ $netProfit >= 0 ? $netMarginColor : 'danger' }} border-3">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Net Profit</p>
                    <h4 class="mb-0 fw-bold text-{{ $netProfit >= 0 ? $netMarginColor : 'danger' }}">
                        Rs.&nbsp;{{ number_format($netProfit, 2) }}
                    </h4>
                    <small class="text-{{ $netProfit >= 0 ? $netMarginColor : 'danger' }}">{{ $r['net_margin'] }}% margin</small>
                    @if($c)
                    @php $g = $growth($r['net_profit'], $c['net_profit']); @endphp
                    <br><small class="{{ $g === null ? 'text-muted' : ($g >= 0 ? 'text-success' : 'text-danger') }}">
                        @if($g !== null)
                            <i class="bi bi-arrow-{{ $g >= 0 ? 'up' : 'down' }}-short"></i>{{ abs($g) }}%
                        @endif
                    </small>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- ── Main Content: P&L Statement + Key Metrics ── --}}
    <div class="row g-4">

        {{-- ── Formal P&L Statement ── --}}
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Profit &amp; Loss Statement</h5>
                    <small class="text-muted">{{ $r['period_label'] }}</small>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Item</th>
                                <th class="text-end">Current Period</th>
                                @if($c)<th class="text-end">Previous Period</th>
                                <th class="text-end">Variance</th>@endif
                            </tr>
                        </thead>
                        <tbody>

                            {{-- ── REVENUE ── --}}
                            <tr class="table-light">
                                <td class="ps-3 fw-bold text-uppercase small">Revenue</td>
                                <td></td>
                                @if($c)<td></td><td></td>@endif
                            </tr>
                            @include('admin.reports.partials.pl-row', [
                                'label'  => 'Gross Sales',
                                'indent' => 1,
                                'curr'   => $r['gross_sales'],
                                'prev'   => $c['gross_sales'] ?? null,
                                'color'  => 'primary',
                            ])
                            @include('admin.reports.partials.pl-row', [
                                'label'  => 'Less: Sales Returns',
                                'indent' => 1,
                                'curr'   => -$r['sales_returns'],
                                'prev'   => isset($c['sales_returns']) ? -$c['sales_returns'] : null,
                                'color'  => 'danger',
                                'deduction' => true,
                            ])
                            @include('admin.reports.partials.pl-row', [
                                'label'  => 'Less: Sales Discounts',
                                'indent' => 1,
                                'curr'   => -$r['sales_discounts'],
                                'prev'   => isset($c['sales_discounts']) ? -$c['sales_discounts'] : null,
                                'color'  => 'danger',
                                'deduction' => true,
                            ])
                            <tr class="fw-bold border-top border-bottom">
                                <td class="ps-3">Net Sales Revenue</td>
                                <td class="text-end text-primary">Rs.&nbsp;{{ number_format($r['net_sales'], 2) }}</td>
                                @if($c)
                                <td class="text-end">Rs.&nbsp;{{ number_format($c['net_sales'], 2) }}</td>
                                <td class="text-end @php $v=$r['net_sales']-$c['net_sales']; echo $v>=0?'text-success':'text-danger' @endphp">
                                    {{ $v >= 0 ? '+' : '' }}Rs.&nbsp;{{ number_format($v, 2) }}
                                </td>
                                @endif
                            </tr>

                            {{-- ── COGS ── --}}
                            <tr class="table-light">
                                <td class="ps-3 fw-bold text-uppercase small">Cost of Goods Sold</td>
                                <td></td>
                                @if($c)<td></td><td></td>@endif
                            </tr>
                            @include('admin.reports.partials.pl-row', [
                                'label'  => 'Opening Inventory',
                                'indent' => 1,
                                'curr'   => $r['opening_inventory'],
                                'prev'   => $c['opening_inventory'] ?? null,
                                'color'  => 'secondary',
                                'italic' => true,
                            ])
                            @include('admin.reports.partials.pl-row', [
                                'label'  => 'Add: Purchases (Gross)',
                                'indent' => 1,
                                'curr'   => $r['purchases_gross'],
                                'prev'   => $c['purchases_gross'] ?? null,
                                'color'  => '',
                            ])
                            @include('admin.reports.partials.pl-row', [
                                'label'  => 'Less: Purchase Returns',
                                'indent' => 1,
                                'curr'   => -$r['purchase_returns'],
                                'prev'   => isset($c['purchase_returns']) ? -$c['purchase_returns'] : null,
                                'color'  => 'danger',
                                'deduction' => true,
                            ])
                            @include('admin.reports.partials.pl-row', [
                                'label'  => 'Less: Purchase Discounts',
                                'indent' => 1,
                                'curr'   => -$r['purchase_discounts'],
                                'prev'   => isset($c['purchase_discounts']) ? -$c['purchase_discounts'] : null,
                                'color'  => 'danger',
                                'deduction' => true,
                            ])
                            @include('admin.reports.partials.pl-row', [
                                'label'  => 'Goods Available for Sale',
                                'indent' => 0,
                                'curr'   => $r['goods_available'],
                                'prev'   => $c['goods_available'] ?? null,
                                'color'  => '',
                                'separator_top' => true,
                            ])
                            @include('admin.reports.partials.pl-row', [
                                'label'  => 'Less: Closing Inventory',
                                'indent' => 1,
                                'curr'   => -$r['closing_inventory'],
                                'prev'   => isset($c['closing_inventory']) ? -$c['closing_inventory'] : null,
                                'color'  => 'danger',
                                'deduction' => true,
                            ])
                            <tr class="fw-bold border-top border-bottom">
                                <td class="ps-3">Cost of Goods Sold</td>
                                <td class="text-end text-warning">Rs.&nbsp;{{ number_format($r['cogs'], 2) }}</td>
                                @if($c)
                                <td class="text-end">Rs.&nbsp;{{ number_format($c['cogs'], 2) }}</td>
                                @php $v=$r['cogs']-$c['cogs']; @endphp
                                <td class="text-end {{ $v<=0?'text-success':'text-danger' }}">
                                    {{ $v >= 0 ? '+' : '' }}Rs.&nbsp;{{ number_format($v, 2) }}
                                </td>
                                @endif
                            </tr>

                            {{-- ── GROSS PROFIT ── --}}
                            <tr class="table-{{ $r['gross_profit'] >= 0 ? 'success' : 'danger' }} fw-bold border-top border-bottom">
                                <td class="ps-3 fs-6">Gross Profit</td>
                                <td class="text-end text-{{ $r['gross_profit'] >= 0 ? 'success' : 'danger' }} fs-6">
                                    Rs.&nbsp;{{ number_format($r['gross_profit'], 2) }}
                                    <span class="badge bg-{{ $grossMarginColor }} ms-1">{{ $r['gross_margin'] }}%</span>
                                </td>
                                @if($c)
                                <td class="text-end">Rs.&nbsp;{{ number_format($c['gross_profit'], 2) }}</td>
                                @php $v=$r['gross_profit']-$c['gross_profit']; @endphp
                                <td class="text-end {{ $v>=0?'text-success':'text-danger' }}">
                                    {{ $v >= 0 ? '+' : '' }}Rs.&nbsp;{{ number_format($v, 2) }}
                                </td>
                                @endif
                            </tr>

                            {{-- ── OPERATING EXPENSES ── --}}
                            <tr class="table-light">
                                <td class="ps-3 fw-bold text-uppercase small">Operating Expenses</td>
                                <td></td>
                                @if($c)<td></td><td></td>@endif
                            </tr>
                            <tr>
                                <td class="ps-4 fst-italic text-muted small" colspan="{{ $c ? 4 : 2 }}">
                                    <i class="bi bi-info-circle me-1"></i>{{ $r['expense_note'] }}
                                </td>
                            </tr>
                            <tr class="fw-semibold border-top">
                                <td class="ps-3">Total Operating Expenses</td>
                                <td class="text-end text-muted">Rs.&nbsp;{{ number_format($r['operating_expenses'], 2) }}</td>
                                @if($c)
                                <td class="text-end text-muted">Rs.&nbsp;{{ number_format($c['operating_expenses'], 2) }}</td>
                                <td class="text-end text-muted">Rs.&nbsp;0.00</td>
                                @endif
                            </tr>

                            {{-- ── NET PROFIT ── --}}
                            <tr class="table-{{ $netProfit >= 0 ? ($netMarginColor === 'success' ? 'success' : 'warning') : 'danger' }} fw-bold border-top border-bottom">
                                <td class="ps-3 fs-6">
                                    {{ $netProfit >= 0 ? 'Net Profit' : 'Net Loss' }}
                                </td>
                                <td class="text-end text-{{ $netProfit >= 0 ? $netMarginColor : 'danger' }} fs-6">
                                    Rs.&nbsp;{{ number_format(abs($netProfit), 2) }}
                                    <span class="badge bg-{{ $netProfit >= 0 ? $netMarginColor : 'danger' }} ms-1">{{ $r['net_margin'] }}%</span>
                                </td>
                                @if($c)
                                @php $cNet = $c['net_profit']; $v = $netProfit - $cNet; @endphp
                                <td class="text-end">Rs.&nbsp;{{ number_format(abs($cNet), 2) }}</td>
                                <td class="text-end {{ $v>=0?'text-success':'text-danger' }}">
                                    {{ $v >= 0 ? '+' : '' }}Rs.&nbsp;{{ number_format($v, 2) }}
                                </td>
                                @endif
                            </tr>

                        </tbody>
                    </table>
                </div>

                {{-- Opening inventory note --}}
                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>COGS method:</strong> <code>products.purchase_price × sold quantity</code>.
                        {{ $r['opening_inventory_note'] }}
                        No expense module installed — operating expenses shown as Rs. 0.
                    </small>
                </div>
            </div>
        </div>

        {{-- ── Key Metrics + Margin Bars ── --}}
        <div class="col-lg-5">
            <div class="row g-3">

                {{-- Margin progress bars --}}
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white"><h6 class="mb-0 fw-semibold">Margin Analysis</h6></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="fw-semibold">Gross Profit Margin</small>
                                    <small class="text-{{ $grossMarginColor }} fw-bold">{{ $r['gross_margin'] }}%</small>
                                </div>
                                <div class="progress" style="height:22px">
                                    <div class="progress-bar bg-{{ $grossMarginColor }}"
                                         style="width:{{ min(max($r['gross_margin'],0), 100) }}%">
                                        {{ $r['gross_margin'] >= 10 ? $r['gross_margin'].'%' : '' }}
                                    </div>
                                </div>
                                @if($c)
                                <small class="text-muted">Previous: {{ $c['gross_margin'] }}%</small>
                                @endif
                            </div>
                            <div>
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="fw-semibold">Net Profit Margin</small>
                                    <small class="text-{{ $netProfit >= 0 ? $netMarginColor : 'danger' }} fw-bold">{{ $r['net_margin'] }}%</small>
                                </div>
                                <div class="progress" style="height:22px">
                                    <div class="progress-bar bg-{{ $netProfit >= 0 ? $netMarginColor : 'danger' }}"
                                         style="width:{{ min(max(abs($r['net_margin']),0), 100) }}%">
                                        {{ abs($r['net_margin']) >= 10 ? $r['net_margin'].'%' : '' }}
                                    </div>
                                </div>
                                @if($c)
                                <small class="text-muted">Previous: {{ $c['net_margin'] }}%</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Key metrics --}}
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white"><h6 class="mb-0 fw-semibold">Key Metrics</h6></div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <tbody>
                                    <tr>
                                        <td class="ps-3 text-muted small">Sales Invoices</td>
                                        <td class="text-end pe-3 fw-semibold">{{ number_format($r['sale_count']) }}</td>
                                        @if($c)<td class="text-end pe-3 text-muted">{{ number_format($c['sale_count']) }}</td>@endif
                                    </tr>
                                    <tr>
                                        <td class="ps-3 text-muted small">Avg Sale Value</td>
                                        <td class="text-end pe-3 fw-semibold">Rs.&nbsp;{{ number_format($r['avg_sale_value'], 2) }}</td>
                                        @if($c)<td class="text-end pe-3 text-muted">Rs.&nbsp;{{ number_format($c['avg_sale_value'], 2) }}</td>@endif
                                    </tr>
                                    <tr>
                                        <td class="ps-3 text-muted small">Purchase Orders</td>
                                        <td class="text-end pe-3 fw-semibold">{{ number_format($r['purchase_count']) }}</td>
                                        @if($c)<td class="text-end pe-3 text-muted">{{ number_format($c['purchase_count']) }}</td>@endif
                                    </tr>
                                    <tr>
                                        <td class="ps-3 text-muted small">Avg Purchase Value</td>
                                        <td class="text-end pe-3 fw-semibold">Rs.&nbsp;{{ number_format($r['avg_purchase_value'], 2) }}</td>
                                        @if($c)<td class="text-end pe-3 text-muted">Rs.&nbsp;{{ number_format($c['avg_purchase_value'], 2) }}</td>@endif
                                    </tr>
                                    <tr class="border-top">
                                        <td class="ps-3 text-muted small">Closing Inventory Value</td>
                                        <td class="text-end pe-3 fw-semibold">Rs.&nbsp;{{ number_format($r['closing_inventory'], 2) }}</td>
                                        @if($c)<td class="text-end pe-3 text-muted">Rs.&nbsp;{{ number_format($c['closing_inventory'], 2) }}</td>@endif
                                    </tr>
                                    <tr>
                                        <td class="ps-3 text-muted small">
                                            Inventory Turnover
                                            <i class="bi bi-info-circle text-muted" title="COGS ÷ Average Inventory" data-bs-toggle="tooltip"></i>
                                        </td>
                                        <td class="text-end pe-3 fw-semibold">{{ $r['inventory_turnover'] }}×</td>
                                        @if($c)<td class="text-end pe-3 text-muted">{{ $c['inventory_turnover'] }}×</td>@endif
                                    </tr>
                                    <tr class="border-top">
                                        <td class="ps-3 text-muted small">Sales Returns</td>
                                        <td class="text-end pe-3 text-danger">Rs.&nbsp;{{ number_format($r['sales_returns'], 2) }}</td>
                                        @if($c)<td class="text-end pe-3 text-muted">Rs.&nbsp;{{ number_format($c['sales_returns'], 2) }}</td>@endif
                                    </tr>
                                    <tr>
                                        <td class="ps-3 text-muted small">Purchase Returns</td>
                                        <td class="text-end pe-3 text-warning">Rs.&nbsp;{{ number_format($r['purchase_returns'], 2) }}</td>
                                        @if($c)<td class="text-end pe-3 text-muted">Rs.&nbsp;{{ number_format($c['purchase_returns'], 2) }}</td>@endif
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        @if($c)
                        <div class="card-footer bg-light">
                            <small class="text-muted">Current period vs. {{ $c['period_label'] }}</small>
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

    </div>{{-- /.row --}}

    {{-- ── Charts ── --}}
    @if(count($r['monthly_data']) > 0)
    <div class="row g-4 mt-1 no-print">

        {{-- Revenue vs Cost chart --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold">Revenue vs Cost &amp; Profit Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="revenueCostChart" height="80"></canvas>
                </div>
            </div>
        </div>

        {{-- Gross profit doughnut --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold">Revenue Breakdown</h6>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="breakdownChart" height="200" width="200"></canvas>
                </div>
            </div>
        </div>

        {{-- Monthly comparison bar chart --}}
        @if(count($r['monthly_data']) > 1)
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold">Monthly Comparison</h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="55"></canvas>
                </div>
            </div>
        </div>
        @endif

    </div>
    @endif

</div>{{-- /.container --}}
@endsection

{{-- ── Partial: P&L table row ── --}}
{{--
    Used via @include. Variables expected:
      $label, $indent (0|1), $curr (float), $prev (float|null),
      $color (bootstrap color string or ''),
      $deduction (bool), $italic (bool), $separator_top (bool)
--}}

@push('styles')
<style>
.btn-xs   { padding:.2rem .45rem; font-size:.75rem; }
.border-3 { border-width:3px !important; }
@media print {
    * { color: #000000 !important; }
    .no-print { display:none !important; }
    .card { border:none !important; box-shadow:none !important; page-break-inside: avoid; }
    .table { font-size:10px; }
    body { font-size:11px; background: white; }
    canvas { display:none !important; }
    .text-danger, .text-success, .text-warning, .text-info, .text-primary, .text-secondary { color: #000000 !important; }
    thead { background-color: white !important; }
    .table-danger td { background-color: white !important; }
    .table-warning td { background-color: white !important; }
}
</style>
@endpush

@push('scripts')
{{-- Chart.js from CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Date presets ──────────────────────────────────────────────────────────────
function setRange(preset) {
    const df = document.querySelector('[name=date_from]');
    const dt = document.querySelector('[name=date_to]');
    const today = new Date();
    const fmt = d => d.toISOString().slice(0,10);
    const q = m => Math.floor(m/3);
    switch(preset) {
        case 'this_month':
            df.value = fmt(new Date(today.getFullYear(), today.getMonth(), 1));
            dt.value = fmt(today); break;
        case 'last_month':
            df.value = fmt(new Date(today.getFullYear(), today.getMonth()-1, 1));
            dt.value = fmt(new Date(today.getFullYear(), today.getMonth(), 0)); break;
        case 'this_quarter':
            df.value = fmt(new Date(today.getFullYear(), q(today.getMonth())*3, 1));
            dt.value = fmt(today); break;
        case 'last_quarter': {
            const lq = q(today.getMonth()) - 1;
            const lqY = lq < 0 ? today.getFullYear()-1 : today.getFullYear();
            const lqM = lq < 0 ? 9 : lq*3;
            df.value = fmt(new Date(lqY, lqM, 1));
            dt.value = fmt(new Date(lqY, lqM+3, 0)); break;
        }
        case 'this_year':
            df.value = fmt(new Date(today.getFullYear(), 0, 1));
            dt.value = fmt(today); break;
        case 'last_year':
            df.value = fmt(new Date(today.getFullYear()-1, 0, 1));
            dt.value = fmt(new Date(today.getFullYear()-1, 11, 31)); break;
    }
}

// ── Chart data from PHP ───────────────────────────────────────────────────────
const monthlyData = @json($r['monthly_data']);
const labels      = monthlyData.map(m => m.label);
const netSales    = monthlyData.map(m => parseFloat(m.net_sales));
const cogsData    = monthlyData.map(m => parseFloat(m.cogs));
const gpData      = monthlyData.map(m => parseFloat(m.gross_profit));
const purchData   = monthlyData.map(m => parseFloat(m.purchases));

// ── Revenue vs Cost line chart ───────────────────────────────────────────────
const rcCtx = document.getElementById('revenueCostChart');
if (rcCtx) {
    new Chart(rcCtx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Net Sales',
                    data: netSales,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13,110,253,0.08)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                },
                {
                    label: 'COGS',
                    data: cogsData,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255,193,7,0.08)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                },
                {
                    label: 'Gross Profit',
                    data: gpData,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25,135,84,0.08)',
                    fill: false,
                    tension: 0.3,
                    pointRadius: 4,
                    borderDash: [5,3],
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: ctx => ' Rs. ' + ctx.parsed.y.toLocaleString(undefined, {minimumFractionDigits:2}),
                    }
                }
            },
            scales: {
                y: {
                    ticks: {
                        callback: v => 'Rs. ' + v.toLocaleString(),
                    }
                }
            }
        },
    });
}

// ── Revenue breakdown doughnut ───────────────────────────────────────────────
const bdCtx = document.getElementById('breakdownChart');
if (bdCtx) {
    const grossSales = {{ $r['gross_sales'] }};
    const returns    = {{ $r['sales_returns'] }};
    const discounts  = {{ $r['sales_discounts'] }};
    const cogs       = {{ $r['cogs'] }};
    const gp         = {{ max($r['gross_profit'], 0) }};

    new Chart(bdCtx, {
        type: 'doughnut',
        data: {
            labels: ['Gross Profit', 'COGS', 'Sales Returns', 'Discounts'],
            datasets: [{
                data: [gp, cogs, returns, discounts],
                backgroundColor: ['#198754','#ffc107','#dc3545','#6c757d'],
                borderWidth: 2,
            }],
        },
        options: {
            responsive: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: ctx => ' Rs. ' + ctx.parsed.toLocaleString(undefined, {minimumFractionDigits:2}),
                    }
                }
            },
        },
    });
}

// ── Monthly comparison bar chart ─────────────────────────────────────────────
const mcCtx = document.getElementById('monthlyChart');
if (mcCtx && labels.length > 1) {
    new Chart(mcCtx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Net Sales',
                    data: netSales,
                    backgroundColor: 'rgba(13,110,253,0.7)',
                    borderRadius: 3,
                },
                {
                    label: 'Purchases',
                    data: purchData,
                    backgroundColor: 'rgba(255,193,7,0.7)',
                    borderRadius: 3,
                },
                {
                    label: 'Gross Profit',
                    data: gpData,
                    backgroundColor: gpData.map(v => v >= 0 ? 'rgba(25,135,84,0.8)' : 'rgba(220,53,69,0.8)'),
                    borderRadius: 3,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: ctx => ' Rs. ' + ctx.parsed.y.toLocaleString(undefined, {minimumFractionDigits:2}),
                    }
                }
            },
            scales: {
                y: {
                    ticks: { callback: v => 'Rs. ' + v.toLocaleString() }
                }
            }
        },
    });
}

// ── Bootstrap tooltips ───────────────────────────────────────────────────────
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el);
});
</script>
@endpush
