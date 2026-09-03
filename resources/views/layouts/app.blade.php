<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inventory System')</title>

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
    </style>

    @yield('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="bi bi-box-seam"></i>
                Inventory Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
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
                                    <a class="dropdown-item" href="{{ route('profile.show') }}">
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

    <!-- Main Content -->
    <main class="flex-grow-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-auto">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-box-seam text-primary"></i> Inventory Management 
                    </h5>
                    <p class="text-muted small">
                        Professional inventory management and accounting solution for businesses of all sizes.
                    </p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h6 class="fw-bold mb-3">Product</h6>
                    <ul class="list-unstyled small">
                        <li><a href="#features" class="text-decoration-none text-muted">Features</a></li>
                        <li><a href="#" class="text-decoration-none text-muted">Documentation</a></li>
                        <li><a href="#" class="text-decoration-none text-muted">Support</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold mb-3">Contact</h6>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-envelope"></i> support@inventory.local<br>
                        <i class="bi bi-telephone"></i> +1 (555) 123-4567
                    </p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <p class="text-muted small mb-0">
                        <i class="bi bi-c-circle"></i> 2026 Inventory Management System. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted small mb-0">
                        <a href="#" class="text-decoration-none text-muted">Privacy Policy</a> | 
                        <a href="#" class="text-decoration-none text-muted">Terms of Service</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @yield('scripts')
</body>
</html>
