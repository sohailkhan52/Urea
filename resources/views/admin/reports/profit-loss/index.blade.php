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

    {{-- Profit & Loss Table --}}
    <div class="card">
        <div class="card-body">
            @if($sales->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Sale No.</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Warehouse</th>
                            <th class="text-end">Net Revenue</th>
                            <th class="text-end">COGS</th>
                            <th class="text-end">Gross Profit/Loss</th>
                            <th class="text-end">Margin %</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                        <tr>
                            <td>
                                <a href="{{ route('admin.reports.profit-loss.show', $sale) }}" class="text-decoration-none fw-bold">
                                    {{ $sale->invoice_number }}
                                </a>
                                @if($sale->returns_count > 0)
                                    <br><small class="text-warning"><i class="bi bi-arrow-return-left me-1"></i>{{ $sale->returns_count }} return(s)</small>
                                @endif
                            </td>
                            <td>{{ $sale->sale_date->format('d M Y') }}</td>
                            <td>
                                @if($sale->customer)
                                    {{ $sale->customer->name }}
                                    @if($sale->customer->phone)
                                        <br><small class="text-muted">{{ $sale->customer->phone }}</small>
                                    @endif
                                @else
                                    {{ $sale->walkin_customer_name ?? 'Walk-in' }}
                                @endif
                            </td>
                            <td>{{ $sale->warehouse->name }}</td>
                            <td class="text-end">
                                @if($sale->has_cost_data)
                                    Rs. {{ number_format($sale->net_revenue, 2) }}
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($sale->has_cost_data)
                                    Rs. {{ number_format($sale->total_cogs, 2) }}
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($sale->has_cost_data)
                                    @if($sale->profit_status === 'profit')
                                        <span class="text-success fw-bold">
                                            <i class="bi bi-arrow-up-circle me-1"></i>Rs. {{ number_format($sale->gross_profit, 2) }}
                                        </span>
                                    @elseif($sale->profit_status === 'loss')
                                        <span class="text-danger fw-bold">
                                            <i class="bi bi-arrow-down-circle me-1"></i>Rs. {{ number_format(abs($sale->gross_profit), 2) }}
                                        </span>
                                    @else
                                        <span class="text-secondary fw-bold">
                                            <i class="bi bi-dash-circle me-1"></i>Rs. 0.00
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($sale->has_cost_data && $sale->net_revenue > 0)
                                    <span class="badge bg-{{ $sale->profit_margin_percentage >= 20 ? 'success' : ($sale->profit_margin_percentage >= 10 ? 'warning' : ($sale->profit_margin_percentage >= 0 ? 'info' : 'danger')) }}">
                                        {{ number_format($sale->profit_margin_percentage, 1) }}%
                                    </span>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($sale->profit_status === 'profit')
                                    <span class="badge bg-success">Profitable</span>
                                @elseif($sale->profit_status === 'loss')
                                    <span class="badge bg-danger">Loss</span>
                                @elseif($sale->profit_status === 'breakeven')
                                    <span class="badge bg-secondary">Break-Even</span>
                                @else
                                    <span class="badge bg-warning">No Cost Data</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.reports.profit-loss.show', $sale) }}" 
                                   class="btn btn-sm btn-outline-primary"
                                   title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $sales->firstItem() ?? 0 }} to {{ $sales->lastItem() ?? 0 }} of {{ $sales->total() }} sales
                </div>
                <div>
                    {{ $sales->links() }}
                </div>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="text-muted mt-2">No sales found for the selected filters.</p>
                <a href="{{ route('admin.reports.profit-loss.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i> Clear Filters
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
