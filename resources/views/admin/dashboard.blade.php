@extends('layouts.admin')

@section('title', 'Dashboard')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="page-header">
    <h1><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>
    <p class="text-muted mb-0">Welcome to Fertilizer Management System</p>
</div>

<div class="row">
    <!-- Total Products -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Total Products</div>
                        <div class="h3 mb-0">0</div>
                    </div>
                    <div class="text-primary" style="font-size: 2.5rem; opacity: 0.3;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Stock Value -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Total Stock Value</div>
                        <div class="h3 mb-0">PKR 0</div>
                    </div>
                    <div class="text-success" style="font-size: 2.5rem; opacity: 0.3;">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Sales -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Today's Sales</div>
                        <div class="h3 mb-0">PKR 0</div>
                    </div>
                    <div class="text-warning" style="font-size: 2.5rem; opacity: 0.3;">
                        <i class="bi bi-cart-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Dealers -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Active Dealers</div>
                        <div class="h3 mb-0">0</div>
                    </div>
                    <div class="text-danger" style="font-size: 2.5rem; opacity: 0.3;">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Sales -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Sales</h5>
                <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-4 d-block mb-2" style="opacity: 0.3;"></i>
                                    No sales recorded yet
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Low Stock Alert</h5>
            </div>
            <div class="card-body">
                <div class="text-center text-muted py-4">
                    <i class="bi bi-check-circle display-4 d-block mb-2" style="opacity: 0.3;"></i>
                    All products are well stocked
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Quick Actions -->
    <div class="col-lg-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="#" class="btn btn-outline-primary w-100">
                            <i class="bi bi-plus-circle me-2"></i>New Sale
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" class="btn btn-outline-success w-100">
                            <i class="bi bi-cart-plus me-2"></i>New Purchase
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" class="btn btn-outline-info w-100">
                            <i class="bi bi-box-seam me-2"></i>Add Product
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" class="btn btn-outline-warning w-100">
                            <i class="bi bi-person-plus me-2"></i>Add Dealer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Companies Overview -->
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i>Fertilizer Companies</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3">
                            <h6 class="text-primary mb-2">
                                <i class="bi bi-building-fill me-2"></i>Fauji Fertilizer Company
                            </h6>
                            <p class="text-muted small mb-1">Products: Sona Urea, Sona DAP</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Active</span>
                                <a href="#" class="btn btn-sm btn-outline-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3">
                            <h6 class="text-success mb-2">
                                <i class="bi bi-building-fill me-2"></i>Engro Fertilizers
                            </h6>
                            <p class="text-muted small mb-1">Products: Engro Urea, Engro DAP</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Active</span>
                                <a href="#" class="btn btn-sm btn-outline-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3">
                            <h6 class="text-info mb-2">
                                <i class="bi bi-building-fill me-2"></i>Fatima Fertilizer
                            </h6>
                            <p class="text-muted small mb-1">Products: Sarsabz Urea</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Active</span>
                                <a href="#" class="btn btn-sm btn-outline-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
