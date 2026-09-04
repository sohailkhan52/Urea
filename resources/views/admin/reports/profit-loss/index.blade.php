@extends('layouts.admin')

@section('title', 'Profit & Loss Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Profit & Loss Report</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-graph-up-arrow me-2"></i>Profit & Loss Report
        </h1>
    </div>

    {{-- Profit/Loss Summary Cards --}}
    @if($totals && $totals->sales_with_cost_data > 0)
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-primary bg-light h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Sales</p>
                            <h4 class="mb-0">{{ number_format($totals->total_sales) }}</h4>
                            <small class="text-muted">{{ $totals->sales_with_cost_data }} with cost data</small>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-receipt fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-success bg-light h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Net Revenue</p>
                            <h4 class="mb-0 text-success">Rs. {{ number_format($totals->total_revenue, 2) }}</h4>
                            <small class="text-muted">After returns & discounts</small>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-cash-stack fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-danger bg-light h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total COGS</p>
                            <h4 class="mb-0 text-danger">Rs. {{ number_format($totals->total_cogs, 2) }}</h4>
                            <small class="text-muted">Cost of goods sold</small>
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-cart-dash fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-{{ $totals->net_profit >= 0 ? 'success' : 'danger' }} bg-light h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Net Profit</p>
                            <h4 class="mb-0 text-{{ $totals->net_profit >= 0 ? 'success' : 'danger' }}">
                                Rs. {{ number_format($totals->net_profit, 2) }}
                            </h4>
                            <small class="text-muted">Margin: {{ number_format($totals->avg_margin, 1) }}%</small>
                        </div>
                        <div class="text-{{ $totals->net_profit >= 0 ? 'success' : 'danger' }}">
                            <i class="bi bi-{{ $totals->net_profit >= 0 ? 'arrow-up-circle' : 'arrow-down-circle' }} fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Profit Breakdown Cards --}}
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-success h-100">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-up-circle text-success fs-1"></i>
                    <h5 class="mt-2">Profitable Sales</h5>
                    <h3 class="text-success">{{ $totals->profitable_sales }}</h3>
                    <p class="text-muted mb-0">Total Profit: Rs. {{ number_format($totals->total_profit, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-danger h-100">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-down-circle text-danger fs-1"></i>
                    <h5 class="mt-2">Loss-Making Sales</h5>
                    <h3 class="text-danger">{{ $totals->loss_sales }}</h3>
                    <p class="text-muted mb-0">Total Loss: Rs. {{ number_format($totals->total_loss, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-secondary h-100">
                <div class="card-body text-center">
                    <i class="bi bi-dash-circle text-secondary fs-1"></i>
                    <h5 class="mt-2">Break-Even Sales</h5>
                    <h3 class="text-secondary">{{ $totals->breakeven_sales }}</h3>
                    <p class="text-muted mb-0">No profit or loss</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">
                <i class="bi bi-funnel me-2"></i>Filters
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.reports.profit-loss.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    {{-- Search --}}
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Invoice number or customer">
                    </div>

                    {{-- Date From --}}
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_from" 
                               name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>

                    {{-- Date To --}}
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_to" 
                               name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>

                    {{-- Customer --}}
                    <div class="col-md-2">
                        <label for="customer_id" class="form-label">Customer</label>
                        <select class="form-select" id="customer_id" name="customer_id">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Family --}}
                    <div class="col-md-2">
                        <label for="family_id" class="form-label">Family</label>
                        <select class="form-select" id="family_id" name="family_id">
                            <option value="">All Families</option>
                            @foreach($families as $family)
                            <option value="{{ $family->id }}" {{ request('family_id') == $family->id ? 'selected' : '' }}>
                                {{ $family->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Warehouse --}}
                    <div class="col-md-2">
                        <label for="warehouse_id" class="form-label">Warehouse</label>
                        <select class="form-select" id="warehouse_id" name="warehouse_id">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Profit Status --}}
                    <div class="col-md-2">
                        <label for="profit_status" class="form-label">Profit Status</label>
                        <select class="form-select" id="profit_status" name="profit_status">
                            <option value="">All Sales</option>
                            <option value="profit" {{ request('profit_status') === 'profit' ? 'selected' : '' }}>Profitable</option>
                            <option value="loss" {{ request('profit_status') === 'loss' ? 'selected' : '' }}>Loss-Making</option>
                            <option value="breakeven" {{ request('profit_status') === 'breakeven' ? 'selected' : '' }}>Break-Even</option>
                        </select>
                    </div>

                    {{-- Created By --}}
                    @if($creators->count() > 0)
                    <div class="col-md-2">
                        <label for="created_by" class="form-label">Created By</label>
                        <select class="form-select" id="created_by" name="created_by">
                            <option value="">All Users</option>
                            @foreach($creators as $creator)
                            <option value="{{ $creator->id }}" {{ request('created_by') == $creator->id ? 'selected' : '' }}>
                                {{ $creator->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Apply Filters
                        </button>
                        <a href="{{ route('admin.reports.profit-loss.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Profit & Loss Statement --}}
    <div class="card">
        <div class="card-body">
            @if($totals)
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h3 class="text-center mb-2 fw-bold">Profit and Loss Statement</h3>
                    
                    {{-- Display Applied Filters/Date Range --}}
                    <div class="text-center mb-4">
                        @if(request('date_from') || request('date_to'))
                            <small class="text-muted">
                                Period: 
                                <strong>{{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('d M Y') : 'Start' }}</strong>
                                to 
                                <strong>{{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('d M Y') : 'End' }}</strong>
                            </small>
                        @else
                            <small class="text-muted">Period: All Transactions</small>
                        @endif
                        
                        @if(request('customer_id'))
                            <br><small class="text-muted">Customer: <strong>{{ $customers->firstWhere('id', request('customer_id'))?->name ?? 'N/A' }}</strong></small>
                        @endif
                        
                        @if(request('warehouse_id'))
                            <br><small class="text-muted">Warehouse: <strong>{{ $warehouses->firstWhere('id', request('warehouse_id'))?->name ?? 'N/A' }}</strong></small>
                        @endif
                    </div>
                    
                    @php
                        $gross_profit = $totals->total_revenue - $totals->total_cogs;
                    @endphp

                    {{-- Revenue Section --}}
                    <div class="p-4 mb-3 rounded" style="background-color: #d1ecf1; border-left: 5px solid #0c5460;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-graph-up fs-3 me-3" style="color: #0c5460;"></i>
                                <h5 class="mb-0 fw-bold">Revenue</h5>
                            </div>
                            <div class="text-end">
                                <h5 class="mb-0 fw-bold">Rs. {{ number_format($totals->total_revenue, 2) }}</h5>
                                <small class="text-muted">{{ number_format($totals->avg_margin, 1) }}%</small>
                            </div>
                        </div>
                    </div>

                    {{-- Cost of Goods Sold Section --}}
                    <div class="p-4 mb-3 rounded" style="background-color: #d1ecf1; border-left: 5px solid #0c5460;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-graph-up fs-3 me-3" style="color: #0c5460;"></i>
                                <h5 class="mb-0 fw-bold">Cost of Goods Sold</h5>
                            </div>
                            <div class="text-end">
                                <h5 class="mb-0 fw-bold">Rs. {{ number_format($totals->total_cogs, 2) }}</h5>
                                <small class="text-muted">{{ $totals->total_revenue > 0 ? number_format(($totals->total_cogs / $totals->total_revenue) * 100, 1) : '0.0' }}%</small>
                            </div>
                        </div>
                    </div>

                    {{-- Gross Profit Section (Amount) --}}
                    <div class="p-4 mb-3 rounded" style="background-color: #d4edda; border-left: 5px solid #155724;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-currency-exchange fs-3 me-3" style="color: #155724;"></i>
                                <h5 class="mb-0 fw-bold">Gross Profit</h5>
                            </div>
                            <div class="text-end">
                                <h5 class="mb-0 fw-bold text-{{ $gross_profit >= 0 ? 'success' : 'danger' }}">
                                    Rs. {{ number_format($gross_profit, 2) }}
                                </h5>
                            </div>
                        </div>
                    </div>

                    {{-- Gross Profit Section (Percentage) --}}
                    <div class="p-4 mb-3 rounded" style="background-color: #d4edda; border-left: 5px solid #155724;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-arrow-down fs-3 me-3" style="color: #155724;"></i>
                                <h5 class="mb-0 fw-bold">Gross Profit</h5>
                            </div>
                            <div class="text-end">
                                <h5 class="mb-0 fw-bold text-{{ $gross_profit >= 0 ? 'success' : 'danger' }}">
                                    {{ number_format($totals->avg_margin, 1) }}%
                                </h5>
                            </div>
                        </div>
                    </div>

                    {{-- Operating Expenses Section --}}
                    <div class="p-4 mb-3 rounded" style="background-color: #d1ecf1; border-left: 5px solid #0c5460;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person-circle fs-3 me-3" style="color: #0c5460;"></i>
                                <h5 class="mb-0 fw-bold">Operating Expenses</h5>
                            </div>
                            <div class="text-end">
                                <i class="bi bi-info-circle text-muted" title="Operating expenses breakdown"></i>
                            </div>
                        </div>
                        <div class="ps-5">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Salaries</span>
                                <span class="text-muted">-</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Rent</span>
                                <span class="text-muted">-</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Utilities</span>
                                <span class="text-muted">-</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Marketing</span>
                                <span class="text-muted">40.0%</span>
                            </div>
                        </div>
                    </div>

                    {{-- Operating Income Section --}}
                    <div class="p-4 mb-3 rounded" style="background-color: #e2e3e5; border-left: 5px solid #383d41;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-graph-up fs-3 me-3" style="color: #383d41;"></i>
                                <h5 class="mb-0 fw-bold">Operating Income</h5>
                            </div>
                            <div class="text-end">
                                <h5 class="mb-0 fw-bold">Rs. {{ number_format($gross_profit, 2) }}</h5>
                                <small class="text-muted">137%</small>
                            </div>
                        </div>
                    </div>

                    {{-- Other Income/Expenses Section --}}
                    <div class="p-4 mb-3 rounded" style="background-color: #f8d7da; border-left: 5px solid #721c24;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-graph-up fs-3 me-3" style="color: #721c24;"></i>
                                <h5 class="mb-0 fw-bold">Other Income/Expenses</h5>
                            </div>
                            <div class="text-end">
                                <h5 class="mb-0 fw-bold">Rs. 0.00</h5>
                                <small class="text-muted">$4.1%</small>
                            </div>
                        </div>
                    </div>

                    {{-- Net Income Section --}}
                    <div class="p-4 mb-3 rounded" style="background-color: #fff3cd; border-left: 5px solid #856404;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-star fs-3 me-3" style="color: #856404;"></i>
                                <h5 class="mb-0 fw-bold">Net Income</h5>
                            </div>
                            <div class="text-end">
                                <h5 class="mb-0 fw-bold text-{{ $totals->net_profit >= 0 ? 'success' : 'danger' }}">
                                    Rs. {{ number_format($totals->net_profit, 2) }}
                                </h5>
                                <small class="text-muted">{{ number_format($totals->avg_margin, 1) }}%</small>
                            </div>
                        </div>
                    </div>

                    @if($totals->sales_with_cost_data > 0)
                    {{-- Summary Note --}}
                    <div class="alert alert-info mt-4">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> This statement is based on {{ number_format($totals->sales_with_cost_data) }} confirmed sales with complete cost data out of {{ number_format($totals->total_sales) }} total confirmed sales
                        @if(request('date_from') || request('date_to'))
                            from {{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('d M Y') : 'start' }} to {{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('d M Y') : 'end' }}
                        @endif
                        . Operating expenses data is not yet integrated into the system.
                    </div>
                    @else
                    <div class="alert alert-warning mt-4">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>No Data:</strong> Please apply date filters or other criteria to generate the profit & loss report.
                    </div>
                    @endif
                </div>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="text-muted mt-2">No profit & loss data available. Please apply filters to generate the report.</p>
                <a href="{{ route('admin.reports.profit-loss.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i> Clear Filters
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
