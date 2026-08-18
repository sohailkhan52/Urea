<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Urea Inventory Management') }} - Professional Inventory Control</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #27ae60;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
            --dark-bg: #2c3e50;
            --card-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            --card-shadow-hover: 0 8px 24px rgba(0, 0, 0, 0.12);
            --border-radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            color: #2c3e50;
        }

        /* ==================== NAVBAR ==================== */
        .navbar {
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--primary-color) !important;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .navbar-brand i {
            font-size: 1.5rem;
            color: var(--secondary-color);
        }

        .navbar-nav .nav-link {
            color: #555 !important;
            font-weight: 500;
            margin: 0 8px;
            transition: color 0.3s ease;
            position: relative;
        }

        .navbar-nav .nav-link:hover {
            color: var(--secondary-color) !important;
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--secondary-color);
            transition: width 0.3s ease;
        }

        .navbar-nav .nav-link:hover::after {
            width: 100%;
        }

        .btn-login-nav {
            background: var(--secondary-color);
            color: white !important;
            padding: 8px 24px !important;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid var(--secondary-color);
        }

        .btn-login-nav:hover {
            background: transparent;
            color: var(--secondary-color) !important;
        }

        /* Profile Icon Styles */
        .user-profile-btn {
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.2rem;
            padding: 0;
        }

        .user-profile-btn:hover {
            background: #2980b9;
            transform: scale(1.1);
        }

        /* Profile Image Styles */
        .user-profile-img {
            background: transparent;
            border: 2px solid var(--secondary-color);
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0;
            overflow: hidden;
        }

        .user-profile-img:hover {
            border-color: #2980b9;
            transform: scale(1.1);
        }

        .user-profile-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .profile-placeholder {
            width: 100%;
            height: 100%;
            background: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
            border-radius: 50%;
        }

        .dropdown-menu {
            background-color: #fff;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .dropdown-item {
            color: #333;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: var(--secondary-color);
            color: white;
        }

        /* ==================== HERO SECTION ==================== */
        .hero {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(52, 152, 219, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 0;
            width: 100%;
            height: 100px;
            background: var(--light-bg);
            clip-path: polygon(0 50%, 100% 0, 100% 100%, 0 100%);
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .hero .subtitle {
            font-size: 1.2rem;
            color: #ecf0f1;
            margin-bottom: 30px;
            line-height: 1.6;
            max-width: 600px;
        }

        .hero-cta {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 40px;
        }

        .btn-primary-hero {
            background: var(--secondary-color);
            color: white;
            padding: 14px 40px;
            border-radius: var(--border-radius);
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-size: 1rem;
        }

        .btn-primary-hero:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(52, 152, 219, 0.3);
            color: white;
        }

        .btn-admin-hero {
            background: var(--accent-color);
            color: white;
            padding: 14px 40px;
            border-radius: var(--border-radius);
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-size: 1rem;
        }

        .btn-admin-hero:hover {
            background: #229954;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(39, 174, 96, 0.3);
            color: white;
        }

        /* ==================== FEATURES SECTION ==================== */
        .features {
            padding: 80px 0;
            background: var(--light-bg);
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .section-title p {
            font-size: 1.1rem;
            color: #7f8c8d;
            max-width: 600px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            border: none;
            border-radius: var(--border-radius);
            padding: 40px 30px;
            height: 100%;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            box-shadow: var(--card-shadow-hover);
            transform: translateY(-8px);
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }

        .feature-card h4 {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .feature-card p {
            color: #7f8c8d;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        /* ==================== WORKFLOW SECTION ==================== */
        .workflow {
            padding: 80px 0;
            background: white;
        }

        .workflow-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .workflow-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .workflow-step {
            flex: 1;
            min-width: 150px;
            text-align: center;
            position: relative;
        }

        .workflow-step-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: var(--secondary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            font-weight: 700;
        }

        .workflow-step h5 {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .workflow-step p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .workflow-arrow {
            display: none;
            font-size: 2rem;
            color: var(--secondary-color);
            margin-bottom: 30px;
        }

        @media (min-width: 768px) {
            .workflow-arrow {
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }

        /* ==================== CTA SECTION ==================== */
        .cta-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(52, 152, 219, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }

        .cta-content {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .cta-content h2 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .cta-content p {
            font-size: 1.1rem;
            color: #ecf0f1;
            margin-bottom: 30px;
        }

        .btn-cta-primary {
            background: var(--secondary-color);
            color: white;
            padding: 16px 50px;
            border-radius: var(--border-radius);
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-size: 1rem;
        }

        .btn-cta-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(52, 152, 219, 0.3);
            color: white;
        }

        .btn-cta-admin {
            background: var(--accent-color);
            color: white;
            padding: 16px 50px;
            border-radius: var(--border-radius);
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-size: 1rem;
        }

        .btn-cta-admin:hover {
            background: #229954;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(39, 174, 96, 0.3);
            color: white;
        }

        /* ==================== FOOTER ==================== */
        footer {
            background: #1a252f;
            color: #95a5a6;
            padding: 40px 0 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }

        .footer-brand {
            font-weight: 600;
            color: white;
            font-size: 1.1rem;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
        }

        /* ==================== RESPONSIVE DESIGN ==================== */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }

            .hero .subtitle {
                font-size: 1rem;
            }

            .hero-cta {
                flex-direction: column;
                gap: 10px;
            }

            .btn-primary-hero,
            .btn-admin-hero,
            .btn-outline-hero {
                width: 100%;
                text-align: center;
            }

            .section-title h2 {
                font-size: 1.8rem;
            }

            .feature-card {
                padding: 30px 20px;
            }

            .workflow-steps {
                flex-direction: column;
                gap: 30px;
            }

            .workflow-arrow {
                display: none;
            }

            .cta-content h2 {
                font-size: 1.6rem;
            }

            .cta-content p {
                font-size: 1rem;
            }

            .btn-cta-primary,
            .btn-cta-admin {
                width: 100%;
                padding: 14px 30px;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
            }
        }

        @media (max-width: 576px) {
            .hero {
                padding: 60px 0;
            }

            .hero h1 {
                font-size: 1.5rem;
            }

            .hero .subtitle {
                font-size: 0.95rem;
            }

            .features,
            .workflow,
            .cta-section {
                padding: 50px 0;
            }

            .section-title h2 {
                font-size: 1.5rem;
            }

            .navbar-brand {
                font-size: 1.1rem;
            }

            .navbar-nav {
                gap: 10px;
            }

            .navbar-nav .nav-link {
                margin: 5px 0;
            }

            .btn-login-nav {
                padding: 6px 16px !important;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- ==================== NAVBAR ==================== -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-lg">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="bi bi-box-seam"></i>
                Urea Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="#features">Features</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#workflow">How It Works</a>
                        </li>
                    @if(!auth()->check())

                        <li class="nav-item">
                            <a class="btn btn-login-nav" href="{{ route('login') }}">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login
                            </a>
                        </li>
                    @else

                        <li class="nav-item">
                            <div class="dropdown">
                                <button class="user-profile-img" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Welcome, {{ auth()->user()->name }}">
                                    @if(auth()->user()->profile_image)
                                        <img src="{{ asset('storage/' . auth()->user()->profile_image) }}" alt="{{ auth()->user()->name }}">
                                    @else
                                        <div class="profile-placeholder">{{ substr(auth()->user()->name, 0, 1) }}</div>
                                    @endif
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li class="dropdown-header">{{ auth()->user()->name }}</li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                            <i class="bi bi-person-check me-2"></i>My Profile
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                            <i class="bi bi-speedometer2 me-2"></i>Go to Dashboard
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <!-- ==================== HERO SECTION ==================== -->
    <section class="hero">
        <div class="container-lg">
            <div class="hero-content">
                <h1>Smart Fertilizer & Inventory Management</h1>
                <p class="subtitle">
                    Manage inventory, warehouses, purchases, sales, customers, suppliers, credit balances, and supplier payments from one centralized system. Streamline your operations with real-time tracking and comprehensive reporting.
                </p>
                <div class="hero-cta">
                    @if(auth()->check())
                        <a href="{{ route('admin.dashboard') }}" class="btn-admin-hero">
                            <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-primary-hero">
                            <i class="bi bi-lock-fill me-2"></i>Login to Dashboard
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== FEATURES SECTION ==================== -->
    <section class="features" id="features">
        <div class="container-lg">
            <div class="section-title">
                <h2>Powerful Features</h2>
                <p>Everything you need to manage your inventory efficiently and professionally</p>
            </div>

            <div class="row g-4">
                <!-- Feature 1 -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-stack"></i>
                        </div>
                        <h4>Smart Inventory Tracking</h4>
                        <p>Track stock accurately across multiple warehouses in real-time. Monitor stock levels, movements, and get alerts for low inventory.</p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-file-earmark-arrow-down"></i>
                        </div>
                        <h4>Purchase Management</h4>
                        <p>Manage supplier purchases and stock entries. Track purchase orders, confirm receipts, and maintain supplier relationships efficiently.</p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <h4>Sales Management</h4>
                        <p>Create and manage sales with professional invoices and payment tracking. Generate reports and maintain customer transaction history.</p>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-credit-card-2-front"></i>
                        </div>
                        <h4>Customer Credit Tracking</h4>
                        <p>Manage customer credit accounts (Udhar). Track outstanding balances, payment history, and maintain secure credit relationships.</p>
                    </div>
                </div>

                <!-- Feature 5 -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-arrow-left-right"></i>
                        </div>
                        <h4>Supplier Payables</h4>
                        <p>Track money owed to suppliers. Manage payable accounts, payment settlements, and maintain transparent supplier relationships.</p>
                    </div>
                </div>

                <!-- Feature 6 -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-building"></i>
                        </div>
                        <h4>Multi-Warehouse Support</h4>
                        <p>Manage inventory across multiple warehouse locations. Transfer stock between warehouses and maintain centralized control.</p>
                    </div>
                </div>

                <!-- Feature 7 -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-bar-chart-fill"></i>
                        </div>
                        <h4>Reports & Analytics</h4>
                        <p>View comprehensive business reports and inventory analytics. Make data-driven decisions with detailed insights and visualizations.</p>
                    </div>
                </div>

                <!-- Feature 8 -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <h4>Secure Access Control</h4>
                        <p>Role-based authentication and secure access management. Protect your data with enterprise-grade security and granular permissions.</p>
                    </div>
                </div>

                <!-- Feature 9 -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                        <h4>Stock Transfers</h4>
                        <p>Manage stock transfers between warehouses with approval workflows. Track transfer status from dispatch to receipt.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== WORKFLOW SECTION ==================== -->
    <section class="workflow" id="workflow">
        <div class="container-lg">
            <div class="section-title">
                <h2>How The System Works</h2>
                <p>Follow the complete workflow from purchase to payment</p>
            </div>

            <div class="workflow-container">
                <div class="workflow-steps">
                    <!-- Step 1 -->
                    <div class="workflow-step">
                        <div class="workflow-step-icon">1</div>
                        <h5>Purchase</h5>
                        <p>Create and manage purchase orders from suppliers</p>
                    </div>

                    <!-- Arrow -->
                    <div class="workflow-arrow">
                        <i class="bi bi-arrow-right"></i>
                    </div>

                    <!-- Step 2 -->
                    <div class="workflow-step">
                        <div class="workflow-step-icon">2</div>
                        <h5>Inventory</h5>
                        <p>Receive and track stock in warehouses</p>
                    </div>

                    <!-- Arrow -->
                    <div class="workflow-arrow">
                        <i class="bi bi-arrow-right"></i>
                    </div>

                    <!-- Step 3 -->
                    <div class="workflow-step">
                        <div class="workflow-step-icon">3</div>
                        <h5>Sales</h5>
                        <p>Create and manage customer sales transactions</p>
                    </div>

                    <!-- Arrow -->
                    <div class="workflow-arrow">
                        <i class="bi bi-arrow-right"></i>
                    </div>

                    <!-- Step 4 -->
                    <div class="workflow-step">
                        <div class="workflow-step-icon">4</div>
                        <h5>Payments</h5>
                        <p>Track customer credit and collect payments</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== CTA SECTION ==================== -->
    <section class="cta-section">
        <div class="container-lg">
            <div class="cta-content">
                <h2>Ready to Manage Your Inventory Efficiently?</h2>
                <p>Start managing your fertilizer inventory and business operations with our professional system today.</p>
                @if(auth()->check())
                    <a href="{{ route('admin.dashboard') }}" class="btn-cta-admin">
                        <i class="bi bi-speedometer2 me-2"></i>Go to Admin Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-cta-primary">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login to Your Dashboard
                    </a>
                @endif
            </div>
        </div>
    </section>

    <!-- ==================== FOOTER ==================== -->
    <footer>
        <div class="container-lg">
            <div class="footer-content">
                <div class="footer-brand">
                    <i class="bi bi-box-seam me-2"></i>Urea Inventory Management System
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'Urea Inventory Management') }}. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
