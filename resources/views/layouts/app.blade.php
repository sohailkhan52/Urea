<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', \App\Models\Company::first()?->name ?? 'Inventory Management')</title>

    <!-- Favicon -->
    @php
        $company = \App\Models\Company::first();
        $favicon = $company && $company->logo ? asset('storage/' . $company->logo) : asset('favicon.ico');
    @endphp
    <link rel="icon" href="{{ $favicon }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#3a4452">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        /* Navbar Styling */
        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color) !important;
        }

        .navbar-brand i {
            margin-right: 0.5rem;
        }

        /* Card Styling */
        .card {
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        }

        /* Button Styling */
        .btn {
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero-content h1 {
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }

        .hero-content .lead {
            font-size: 1.15rem;
            line-height: 1.6;
        }

        /* Gradient Background */
        .bg-gradient-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
        }

        /* Section Spacing */
        section {
            padding: 3rem 0;
        }

        /* Footer */
        footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            padding: 2rem 0;
            margin-top: 3rem;
        }

        /* Utility Classes */
        .text-gradient {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Feature Cards */
        .feature-card {
            text-align: center;
            padding: 2rem 1.5rem;
            height: 100%;
            background: white;
            border-radius: 0.75rem;
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.1);
        }

        .feature-card i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }

        /* Badge Styling */
        .badge {
            padding: 0.5rem 0.75rem;
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }

            section {
                padding: 2rem 0;
            }
        }

        @media (min-width: 992px) and (max-width: 1024px) {
            .navbar-expand-lg .navbar-toggler {
                display: block;
            }

            .navbar-expand-lg .navbar-collapse:not(.show) {
                display: none !important;
            }

            .navbar-expand-lg .navbar-collapse.show {
                display: block !important;
            }
        }

        .mobile-user-menu-modal .modal-content {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.2);
        }

        .mobile-user-menu-modal .modal-dialog-centered {
            position: absolute;
            top: 80px;
            right: 12px;
            width: min(280px, calc(100vw - 24px));
            max-width: 280px;
            min-height: 0;
            margin: 0;
            align-items: flex-start;
        }

        @media (min-width: 576px) and (max-width: 767.98px) {
            .mobile-user-menu-modal .modal-dialog-centered {
                right: calc((100vw - 540px) / 2 + 12px);
            }
        }

        @media (min-width: 768px) and (max-width: 991.98px) {
            .mobile-user-menu-modal .modal-dialog-centered {
                right: calc((100vw - 720px) / 2 + 12px);
            }
        }

        @media (min-width: 992px) and (max-width: 1024px) {
            .mobile-user-menu-modal .modal-dialog-centered {
                right: calc((100vw - 960px) / 2 + 12px);
            }
        }

        .mobile-user-menu-modal .modal-action {
            min-height: 44px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            padding: 0.5rem 0.75rem;
        }

        .mobile-user-menu-modal .modal-header {
            padding: 0.75rem 0.75rem 0;
        }

        .mobile-user-menu-modal .modal-body {
            padding: 0.75rem;
        }

        .mobile-user-menu-modal .modal-body > .d-flex {
            padding: 0.625rem !important;
            margin-bottom: 0.75rem !important;
        }

        .mobile-user-menu-modal .modal-body > .d-flex img {
            width: 40px !important;
            height: 40px !important;
        }
    </style>

    @yield('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            @php
                $company = \App\Models\Company::first();
                $companyName = $company?->name ?? 'Inventory Management';
                $companyLogo = $company?->logo ? asset('storage/' . $company->logo) : null;
            @endphp
            <a class="navbar-brand" href="{{ route('home') }}">
                @if($companyLogo)
                    <img src="{{ $companyLogo }}" alt="Logo" style="max-height: 30px; width: auto; margin-right: 10px;">
                @else
                    <i class="bi bi-box-seam"></i>
                @endif
                {{ $companyName }}
            </a>
            <button class="navbar-toggler" type="button"
                    @auth data-bs-toggle="modal" data-bs-target="#mobileUserMenuModal" aria-label="Open menu"
                    @else data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation" @endauth>
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="#features">Features</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Sign In</a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="bi bi-person me-2"></i>Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    @auth
        <div class="modal fade mobile-user-menu-modal" id="mobileUserMenuModal" tabindex="-1" aria-labelledby="mobileUserMenuTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="mobileUserMenuTitle">Menu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-3">
                        <div class="d-flex align-items-center gap-3 p-3 mb-3 bg-light rounded-3">
                            <img src="{{ auth()->user()->profile_image_url }}" alt="{{ auth()->user()->name }}"
                                 class="rounded-circle" style="width: 48px; height: 48px; object-fit: cover;">
                            <div class="min-w-0">
                                <div class="fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                                <small class="text-muted text-truncate d-block">{{ auth()->user()->email }}</small>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <a class="btn btn-light text-start modal-action" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person text-primary"></i> Profile
                            </a>
                            <a class="btn btn-light text-start modal-action" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer2 text-primary"></i> Dashboard
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-light text-start text-danger modal-action w-100">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endauth

    <!-- Main Content -->
    <main class="flex-grow-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-auto">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-6 mb-4 mb-md-0">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-box-seam text-primary"></i> Inventory Management 
                    </h5>
                    <p class="text-muted small">
                        Professional inventory management and accounting solution for businesses of all sizes.
                    </p>
                </div>
                <div class="col-md-6 mb-4 mb-md-0">
                    <h6 class="fw-bold mb-3">Address</h6>
                    @php($footerCompany = \App\Models\Company::first())
                    @php($footerPhones = array_filter([$footerCompany?->phone, $footerCompany?->additional_number], fn($value) => !empty($value)))
                    <p class="text-muted small mb-1">
                        <i class="bi bi-telephone me-1"></i>
                        {{ !empty($footerPhones) ? implode(' / ', $footerPhones) : 'Phone not available' }}
                    </p>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-geo-alt me-1"></i>
                        {{ $footerCompany?->address ?? 'Address not available' }}
                    </p>
                </div>
            </div>
            <hr>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @yield('scripts')
</body>
</html>
