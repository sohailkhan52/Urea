@extends('layouts.app')

@section('title', 'Welcome to Inventory Management System - Stock & Order Control')

@section('content')
<div class="min-vh-100 bg-gradient-to-br from-blue-50 to-indigo-100 d-flex align-items-center">
    <div class="container">
        <!-- Hero Section -->
        <div class="row align-items-center mb-5">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="hero-content">
                    <h1 class="display-4 fw-bold text-dark mb-4">
                        <i class="bi bi-box-seam me-2 text-primary"></i>Inventory Management System
                    </h1>
                    <p class="lead text-muted mb-4">
                        Professional-grade inventory and order management solution. Control stock levels, 
                        manage suppliers, track sales, and optimize your supply chain efficiently.
                    </p>
                    
                    <div class="d-flex gap-3 mb-5">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-5">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                        </a>
                        <a href="#features" class="btn btn-outline-primary btn-lg px-5">
                            <i class="bi bi-star me-2"></i>Learn More
                        </a>
                    </div>

                    <div class="row g-3">
                        <div class="col-auto">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-success bg-opacity-10 me-3">
                                    <i class="bi bi-graph-up text-success"></i>
                                </div>
                                <div>
                                    <p class="mb-0 small text-muted">Performance</p>
                                    <p class="mb-0 fw-bold">Real-time Tracking</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-warning bg-opacity-10 me-3">
                                    <i class="bi bi-shield-check text-warning"></i>
                                </div>
                                <div>
                                    <p class="mb-0 small text-muted">Security</p>
                                    <p class="mb-0 fw-bold">Enterprise Grade</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="hero-image">
                    <div class="card border-0 shadow-lg">
                        <div class="card-body p-5">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title mb-0">Core Features</h5>
                                <span class="badge bg-primary">Pro</span>
                            </div>
                            
                            <div class="list-group list-group-flush">
                                <div class="list-group-item border-0 px-0 py-3">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-check2-circle text-success me-3 mt-1"></i>
                                        <div>
                                            <h6 class="mb-1">Stock Management</h6>
                                            <p class="mb-0 small text-muted">Real-time inventory tracking across warehouses</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item border-0 px-0 py-3">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-check2-circle text-success me-3 mt-1"></i>
                                        <div>
                                            <h6 class="mb-1">Order Processing</h6>
                                            <p class="mb-0 small text-muted">Streamlined sales and purchase order management</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item border-0 px-0 py-3">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-check2-circle text-success me-3 mt-1"></i>
                                        <div>
                                            <h6 class="mb-1">Supplier Control</h6>
                                            <p class="mb-0 small text-muted">Manage supplier relationships and payment tracking</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item border-0 px-0 py-3">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-check2-circle text-success me-3 mt-1"></i>
                                        <div>
                                            <h6 class="mb-1">Financial Reports</h6>
                                            <p class="mb-0 small text-muted">Comprehensive accounting and profit analysis</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item border-0 px-0 py-3">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-check2-circle text-success me-3 mt-1"></i>
                                        <div>
                                            <h6 class="mb-1">Multi-Location</h6>
                                            <p class="mb-0 small text-muted">Manage multiple warehouses and branches</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item border-0 px-0 py-3">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-check2-circle text-success me-3 mt-1"></i>
                                        <div>
                                            <h6 class="mb-1">Access Control</h6>
                                            <p class="mb-0 small text-muted">Role-based permissions and audit trails</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Statistics -->
        <div class="row g-4 mb-5" id="features">
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm text-center p-4 feature-card">
                    <i class="bi bi-boxes display-4 text-primary mb-3"></i>
                    <h5 class="mb-2">Stock Control</h5>
                    <p class="text-muted small">Monitor inventory levels and optimize stock</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm text-center p-4 feature-card">
                    <i class="bi bi-receipt display-4 text-success mb-3"></i>
                    <h5 class="mb-2">Sales Orders</h5>
                    <p class="text-muted small">Manage customer orders and fulfillment</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm text-center p-4 feature-card">
                    <i class="bi bi-cart-check display-4 text-warning mb-3"></i>
                    <h5 class="mb-2">Purchasing</h5>
                    <p class="text-muted small">Control supplier orders and receivables</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm text-center p-4 feature-card">
                    <i class="bi bi-graph-up-arrow display-4 text-info mb-3"></i>
                    <h5 class="mb-2">Reports</h5>
                    <p class="text-muted small">Data-driven insights and analytics</p>
                </div>
            </div>
        </div>

        <!-- Benefits Section -->
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="text-center mb-4 fw-bold">Why Choose Our System?</h2>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="d-flex">
                            <div class="icon-circle bg-primary bg-opacity-10 me-3 flex-shrink-0">
                                <i class="bi bi-lightning-fill text-primary"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold">Fast & Efficient</h6>
                                <p class="text-muted small">Streamline operations and reduce manual work</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex">
                            <div class="icon-circle bg-success bg-opacity-10 me-3 flex-shrink-0">
                                <i class="bi bi-check2-all text-success"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold">Accurate Data</h6>
                                <p class="text-muted small">Eliminate errors and maintain data integrity</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex">
                            <div class="icon-circle bg-warning bg-opacity-10 me-3 flex-shrink-0">
                                <i class="bi bi-shield-lock text-warning"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold">Secure System</h6>
                                <p class="text-muted small">Protected data with enterprise-level security</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex">
                            <div class="icon-circle bg-info bg-opacity-10 me-3 flex-shrink-0">
                                <i class="bi bi-graph-up text-info"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold">Scalable</h6>
                                <p class="text-muted small">Grows with your business needs</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card border-0 bg-primary bg-gradient text-white shadow-lg">
                    <div class="card-body p-5 text-center">
                        <h3 class="card-title mb-3">Ready to Optimize Your Inventory?</h3>
                        <p class="card-text mb-4 lead">
                            Sign in to access the full power of our inventory management system.
                        </p>
                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                            <a href="{{ route('login') }}" class="btn btn-light btn-lg px-4">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In Now
                            </a>
  
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="text-center mt-5 pt-4 border-top border-light">
            <p class="text-muted mb-2">
                <i class="bi bi-c-circle"></i> 2026 Inventory Management System. All rights reserved.
            </p>
            <p class="text-muted small">
                @php
                    $superAdmin = \App\Models\User::whereHas('roles', function($q) {
                        $q->where('slug', 'super-admin');
                    })->first();
                @endphp
                For questions or support, please <a href="mailto:{{ $superAdmin ? $superAdmin->email : 'support@inventory.local' }}" class="text-muted text-decoration-none">contact your system administrator</a>.
            </p>
        </div>
    </div>
</div>

<style>
    .icon-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .hero-image {
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }

    .bg-gradient-to-br {
        background: linear-gradient(135deg, #f0f4ff 0%, #e6f2ff 100%);
    }

    .feature-card {
        transition: all 0.3s ease;
    }

    .feature-card:hover {
        border-color: var(--primary-color) !important;
        box-shadow: 0 5px 15px rgba(13, 110, 253, 0.1) !important;
    }
</style>
@endsection
