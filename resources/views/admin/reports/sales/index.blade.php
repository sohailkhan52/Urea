@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Sales Reports</h1>
            <p class="text-muted mb-0">Choose a sales report to view</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3"><i class="bi bi-calendar-day text-primary" style="font-size:3rem"></i></div>
                    <h5 class="card-title">Daily Sales Report</h5>
                    <p class="card-text text-muted">View sales transactions by date range</p>
                    <a href="{{ route('admin.reports.sales.daily') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-right me-1"></i>View Report
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3"><i class="bi bi-box-seam text-success" style="font-size:3rem"></i></div>
                    <h5 class="card-title">Product-Wise Sales</h5>
                    <p class="card-text text-muted">Sales analysis grouped by product</p>
                    <a href="{{ route('admin.reports.sales.product-wise') }}" class="btn btn-success">
                        <i class="bi bi-arrow-right me-1"></i>View Report
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3"><i class="bi bi-people text-info" style="font-size:3rem"></i></div>
                    <h5 class="card-title">Customer-Wise Sales</h5>
                    <p class="card-text text-muted">Sales analysis grouped by customer</p>
                    <a href="{{ route('admin.reports.sales.customer-wise') }}" class="btn btn-info">
                        <i class="bi bi-arrow-right me-1"></i>View Report
                    </a>
                </div>
            </div>
        </div>
        @if(auth()->user()->isSuperAdmin())
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3"><i class="bi bi-building text-warning" style="font-size:3rem"></i></div>
                    <h5 class="card-title">Warehouse-Wise Sales</h5>
                    <p class="card-text text-muted">Compare performance across warehouses</p>
                    <a href="{{ route('admin.reports.sales.warehouse-wise') }}" class="btn btn-warning">
                        <i class="bi bi-arrow-right me-1"></i>View Report
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
