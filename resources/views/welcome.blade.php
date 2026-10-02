@extends('layouts.app')

@section('title', 'Welcome to Inventory Management System - Stock & Order Control')

@section('content')
<div class="min-vh-100 bg-gradient-to-br from-blue-50 to-indigo-100 d-flex align-items-center landing-page">
    <div class="container">
        <!-- Hero Section -->
        <div class="row align-items-center mb-5 landing-hero">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="hero-content">
                    <h1 class="display-4 fw-bold text-dark mb-4">
                        <i class="bi bi-box-seam me-2 text-primary"></i>Inventory Management System
                    </h1>
                    <p class="lead text-muted mb-4">
                        Professional-grade inventory and order management solution. Control stock levels, 
                        manage suppliers, track sales, and optimize your supply chain efficiently.
                    </p>
                    
                    <div class="d-flex gap-3 mb-5 hero-actions">
                        @guest
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                            </a>
                            <a href="#features" class="btn btn-outline-primary btn-lg px-5">
                                <i class="bi bi-star me-2"></i>Learn More
                            </a>
                        @else
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-lg px-5">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        @endguest
                    </div>

                    <div class="row g-3 hero-highlights">
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
                    <div class="card border-0 shadow-lg hero-feature-card">
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
        <div class="row g-4 mb-5 landing-features" id="features">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm text-center p-4 feature-card">
                    <i class="bi bi-boxes display-4 text-primary mb-3"></i>
                    <h5 class="mb-2">Stock Control</h5>
                    <p class="text-muted small">Monitor inventory levels and optimize stock</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm text-center p-4 feature-card">
                    <i class="bi bi-receipt display-4 text-success mb-3"></i>
                    <h5 class="mb-2">Sales Orders</h5>
                    <p class="text-muted small">Manage customer orders and fulfillment</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm text-center p-4 feature-card">
                    <i class="bi bi-cart-check display-4 text-warning mb-3"></i>
                    <h5 class="mb-2">Purchasing</h5>
                    <p class="text-muted small">Control supplier orders and receivables</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
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

    </div>
</div>

<style>
    .landing-page {
        width: 100%;
        padding: 2.5rem 0;
    }

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

    @media (max-width: 991.98px) {
        .landing-page {
            align-items: flex-start !important;
            padding: 2rem 0;
        }

        .landing-hero {
            margin-bottom: 2.5rem !important;
        }

        .hero-content h1 {
            font-size: clamp(2rem, 5vw, 3rem);
            line-height: 1.15;
        }

        .hero-content .lead {
            font-size: 1.05rem;
        }

        .hero-actions {
            flex-wrap: wrap;
            gap: .75rem !important;
            margin-bottom: 2rem !important;
        }

        .hero-actions .btn {
            flex: 1 1 calc(50% - .5rem);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 0;
            padding: .75rem 1rem;
            text-align: center;
            white-space: nowrap;
        }

        .hero-feature-card .card-body {
            padding: 2rem !important;
        }
    }

    @media (max-width: 767.98px) {
        .hero-content h1 {
            font-size: clamp(1.9rem, 6vw, 2.6rem);
        }

        .hero-content .lead {
            font-size: 1rem;
            line-height: 1.55;
        }

        .hero-actions .btn {
            min-height: 52px;
            font-size: 1rem;
        }

        .hero-feature-card .card-body {
            padding: 1.4rem !important;
        }

        .hero-feature-card .list-group-item {
            padding-top: .65rem !important;
            padding-bottom: .65rem !important;
        }

        .feature-card {
            padding: 1.25rem .75rem !important;
        }

        .feature-card i {
            font-size: 2rem;
            margin-bottom: .65rem;
        }

        .feature-card h5 {
            font-size: .98rem;
        }

        .feature-card p {
            margin-bottom: 0;
            font-size: .78rem;
        }

        .landing-page .container {
            padding-right: 1rem;
            padding-left: 1rem;
        }
    }

    @media (max-width: 575.98px) {
        .navbar .container {
            flex-wrap: nowrap;
            gap: .5rem;
        }

        .navbar-brand {
            display: flex;
            flex: 1 1 auto;
            align-items: center;
            min-width: 0;
            margin-right: 0;
            overflow: hidden;
            font-size: clamp(1rem, 4.5vw, 1.25rem);
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .navbar-brand img {
            flex: 0 0 auto;
            max-width: 38px;
            max-height: 28px !important;
            margin-right: .45rem !important;
            object-fit: contain;
        }

        .navbar-toggler {
            flex: 0 0 auto;
            padding: .3rem .5rem;
        }

        .landing-page {
            padding: 1.25rem 0 2rem;
        }

        .landing-page .container {
            padding-right: .9rem;
            padding-left: .9rem;
        }

        .landing-hero {
            margin-bottom: 1.75rem !important;
        }

        .hero-content h1 {
            margin-bottom: 1rem !important;
            font-size: clamp(1.8rem, 7.5vw, 2.3rem);
            line-height: 1.12;
        }

        .hero-content .lead {
            margin-bottom: 1.2rem !important;
            font-size: .98rem;
            line-height: 1.5;
        }

        .hero-actions {
            flex-wrap: nowrap;
            gap: .6rem !important;
            margin-bottom: 1.5rem !important;
        }

        .hero-actions .btn {
            flex: 1 1 0;
            min-height: 48px;
            padding: .65rem .35rem;
            font-size: .9rem;
            line-height: 1.2;
        }

        .hero-actions .btn i {
            margin-right: .3rem !important;
        }

        .hero-highlights {
            --bs-gutter-y: .65rem;
        }

        .hero-highlights > .col-auto {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .hero-highlights .icon-circle {
            width: 42px;
            height: 42px;
            font-size: 1.2rem;
        }

        .hero-feature-card .card-body {
            padding: 1rem !important;
        }

        .hero-feature-card .card-title {
            font-size: 1rem;
        }

        .landing-features {
            --bs-gutter-x: .75rem;
            --bs-gutter-y: .75rem;
        }

        .landing-features .feature-card {
            min-height: 150px;
        }

        .landing-page h2 {
            font-size: 1.4rem;
        }
    }

    @media (max-width: 359.98px) {
        .hero-actions .btn {
            font-size: .82rem;
            padding-right: .2rem;
            padding-left: .2rem;
        }

        .hero-actions .btn i {
            margin-right: .15rem !important;
        }

        .landing-features .feature-card {
            min-height: 160px;
        }
    }
</style>
@endsection
