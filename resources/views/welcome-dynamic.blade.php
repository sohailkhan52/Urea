@php
    use App\Services\WelcomePageService;
    $service = new WelcomePageService();
    $data = $service->getFrontendData();
    $settings = $data['settings'];
    // Note: We don't use features and workflow_steps from database
    // All features, workflow, and other sections are now static/hardcoded
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $settings->company_name }} - Professional Inventory Control</title>

    <link rel="icon" href="{{ \App\Helpers\CompanyHelper::getFaviconUrl() }}" sizes="any">

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

        /* Navbar */
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

        .navbar-brand img {
            max-height: 40px;
            width: auto;
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

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
            min-height: 500px;
            display: flex;
            align-items: center;
        }

        @if($settings->hero_background_image)
        .hero {
            background: url('{{ $settings->getBackgroundImageUrlAttribute() }}') center/cover;
            background-attachment: fixed;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(44, 62, 80, 0.85);
        }
        @endif

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

        .btn-secondary-hero {
            background: transparent;
            color: white;
            padding: 14px 40px;
            border-radius: var(--border-radius);
            font-weight: 600;
            border: 2px solid white;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-size: 1rem;
        }

        .btn-secondary-hero:hover {
            background: white;
            color: var(--primary-color);
        }

        /* Features */
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

        .feature-card:hover {
            box-shadow: var(--card-shadow-hover);
            transform: translateY(-8px);
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

        /* Workflow */
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

        /* CTA */
        .cta-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            position: relative;
            overflow: hidden;
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

        .btn-cta {
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

        .btn-cta:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(52, 152, 219, 0.3);
            color: white;
        }

        /* Footer */
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

        /* Responsive */
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
            .btn-secondary-hero {
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

            .cta-content h2 {
                font-size: 1.6rem;
            }

            .cta-content p {
                font-size: 1rem;
            }

            .btn-cta {
                width: 100%;
                padding: 14px 30px;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-lg">
            <a class="navbar-brand" href="{{ route('home') }}">
                @if($settings->company_logo)
                    <img src="{{ $settings->getLogoUrlAttribute() }}" alt="{{ $settings->company_name }}">
                @else
                    <i class="bi bi-box-seam"></i>
                @endif
                {{ $settings->company_short_name ?? $settings->company_name }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#hero">Home</a>
                    </li>
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
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    @if($settings->show_hero_section)
    <section class="hero" id="hero">
        <div class="container-lg">
            <div class="hero-content">
                <h1>{{ $settings->hero_title }}</h1>
                @if(isset($settings->company_description) && $settings->company_description)
                    <p class="subtitle">{{ $settings->company_description }}</p>
                @endif
                <div class="hero-cta">
                    @if($settings->hero_primary_button_text)
                        @if(auth()->check())
                            <a href="{{ $settings->hero_primary_button_url ?? route('admin.dashboard') }}" class="btn-primary-hero">
                                {{ $settings->hero_primary_button_text }}
                            </a>
                        @else
                            <a href="{{ $settings->hero_primary_button_url ?? route('login') }}" class="btn-primary-hero">
                                {{ $settings->hero_primary_button_text }}
                            </a>
                        @endif
                    @endif
                    @if($settings->hero_secondary_button_text)
                        <a href="{{ $settings->hero_secondary_button_url ?? '#' }}" class="btn-secondary-hero">
                            {{ $settings->hero_secondary_button_text }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Features Section (Static) -->
    <section class="features" id="features">
        <div class="container-lg">
            <div class="section-title">
                <h2>Powerful Features</h2>
                <p>Everything you need to manage your inventory efficiently and professionally</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <h4>Smart Inventory Tracking</h4>
                        <p>Track your inventory in real-time across multiple warehouses. Get instant updates on stock levels with low-stock alerts.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-cart"></i>
                        </div>
                        <h4>Purchase Management</h4>
                        <p>Manage supplier orders and track delivery status. Bulk purchasing with automated supplier relationship management.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <h4>Sales Management</h4>
                        <p>Create and track sales orders with ease. Automated invoicing and delivery tracking with customer communication tools.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <h4>Customer Credit Tracking</h4>
                        <p>Monitor Udhar and outstanding customer payments while managing customer debt efficiently.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-file-earmark-check"></i>
                        </div>
                        <h4>Supplier Payables</h4>
                        <p>Track money owed to suppliers. Manage payments schedules, view aging reports, and keep good supplier relationships.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-building"></i>
                        </div>
                        <h4>Multi-Warehouse Support</h4>
                        <p>Manage multiple warehouses and branches. Transfer stock between locations, track inventory per location with centralized control.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h4>Reports & Analytics</h4>
                        <p>Comprehensive reporting and analytics. Make data-driven decisions with detailed insights into your business operations.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4>Secure Access Control</h4>
                        <p>Role-based user management. Protect your data with advanced security. Define user roles with specific permissions.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-arrow-left-right"></i>
                        </div>
                        <h4>Stock Transfers</h4>
                        <p>Manage stock movement between warehouses with approval workflows. Track transfer status in real-time.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Workflow Section (Static) -->
    <section class="workflow" id="workflow">
        <div class="container-lg">
            <div class="section-title">
                <h2>How The System Works</h2>
                <p>Follow the complete workflow from product management to payment tracking</p>
            </div>
            <div class="workflow-container">
                <div class="workflow-steps">
                    <div class="workflow-step">
                        <div class="workflow-step-icon">
                            <i class="bi bi-1-circle"></i>
                        </div>
                        <h5>Register</h5>
                        <p>Create your account and set up company profile with user roles</p>
                    </div>
                    <div class="workflow-step">
                        <div class="workflow-step-icon">
                            <i class="bi bi-2-circle"></i>
                        </div>
                        <h5>Setup</h5>
                        <p>Set up your products, warehouses, and add suppliers/customers</p>
                    </div>
                    <div class="workflow-step">
                        <div class="workflow-step-icon">
                            <i class="bi bi-3-circle"></i>
                        </div>
                        <h5>Sales</h5>
                        <p>Create and manage orders with automated inventory updates</p>
                    </div>
                    <div class="workflow-step">
                        <div class="workflow-step-icon">
                            <i class="bi bi-4-circle"></i>
                        </div>
                        <h5>Track</h5>
                        <p>Track payments, credit, and view comprehensive business reports</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section (Static) -->
    <section class="cta-section">
        <div class="container-lg">
            <div class="cta-content">
                <h2>Ready to Manage Your Inventory Efficiently?</h2>
                <p>Start managing your fertilizer inventory and business operations with our professional system today</p>
                @if(auth()->check())
                    <a href="{{ route('admin.dashboard') }}" class="btn-cta">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-cta">
                        Get Started
                    </a>
                @endif
            </div>
        </div>
    </section>

    <!-- Footer (Static with Dynamic Company Name) -->
    <footer>
        <div class="container-lg">
            <div class="footer-content">
                <div class="footer-brand">
                    {{ $settings->company_name }}
                </div>
                <p>Professional fertilizer inventory management system designed for efficiency and growth</p>
            </div>
            <div class="footer-bottom">
                © {{ date('Y') }} {{ $settings->company_name }}. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
