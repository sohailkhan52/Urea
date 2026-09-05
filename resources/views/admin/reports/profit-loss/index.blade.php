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


</div>
@endsection
