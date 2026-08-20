<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Fertilizer Management System') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    @stack('styles')

    <style>
        :root {
            --sidebar-width: 260px;
            --topbar-height: 60px;
        }

        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            color: #ecf0f1;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 1040;
        }

        .sidebar-brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.2);
        }

        .sidebar-brand h4 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #fff;
        }

        .sidebar-brand small {
            color: #95a5a6;
            font-size: 0.75rem;
        }

        .sidebar-nav {
            padding: 15px 0;
        }

        .nav-section-title {
            padding: 15px 20px 8px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #95a5a6;
            letter-spacing: 0.5px;
        }

        .sidebar-nav .nav-link {
            padding: 12px 20px;
            color: #ecf0f1;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .sidebar-nav .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: #3498db;
        }

        .sidebar-nav .nav-link.active {
            background: rgba(52, 152, 219, 0.2);
            border-left-color: #3498db;
            font-weight: 600;
        }

        .sidebar-nav .nav-link i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        /* Main Content Area */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
            overflow-x: hidden;
            width: calc(100% - var(--sidebar-width));
            box-sizing: border-box;
        }

        /* Top Navbar */
        .topbar {
            height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid #e3e6f0;
            display: flex;
            align-items: center;
            padding: 0 25px;
            position: sticky;
            top: 0;
            z-index: 1030;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .topbar .breadcrumb {
            margin: 0;
            background: transparent;
            padding: 0;
        }

        .topbar-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Content Area */
        .content {
            padding: 25px;
        }

        /* Page Header */
        .page-header {
            background: #fff;
            padding: 20px 25px;
            margin: -25px -25px 25px;
            border-bottom: 1px solid #e3e6f0;
        }

        .page-header h1 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 600;
            color: #2c3e50;
        }

        /* Cards */
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 25px;
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #e3e6f0;
            padding: 15px 20px;
            font-weight: 600;
        }

        /* Stat Cards */
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.primary {
            border-left-color: #3498db;
        }

        .stat-card.success {
            border-left-color: #27ae60;
        }

        .stat-card.warning {
            border-left-color: #f39c12;
        }

        .stat-card.danger {
            border-left-color: #e74c3c;
        }

        /* Alerts */
        .alert {
            border: none;
            border-left: 4px solid;
        }

        /* Toggle Sidebar Button */
        .toggle-sidebar {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #2c3e50;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }

            .sidebar.show {
                margin-left: 0;
            }

            .main-wrapper {
                margin-left: 0;
            }

            .toggle-sidebar {
                display: block;
            }
        }

        /* Utilities */
        .badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        /* Fix dropdown menu clipping in responsive tables */
        .table-responsive {
            overflow: visible !important;
        }

        .table td .dropdown {
            position: static;
        }

        .table td .dropdown-menu {
            position: absolute;
            z-index: 1050;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            @php
                try {
                    $sidebarSettings = \App\Models\WelcomePageSetting::first();
                    $companyShortName = $sidebarSettings?->company_short_name ?? $sidebarSettings?->company_name ?? 'DeraNexa';
                    $companyFullName = $sidebarSettings?->company_name ?? 'DeraNexa';
                    $companyLogo = $sidebarSettings?->company_logo ? asset('storage/' . $sidebarSettings->company_logo) : null;
                } catch (\Exception $e) {
                    $companyShortName = 'DeraNexa';
                    $companyFullName = 'DeraNexa';
                    $companyLogo = null;
                }
            @endphp
            @if($companyLogo)
                <img src="{{ $companyLogo }}" alt="Logo" style="max-height: 40px; width: auto; margin-bottom: 10px;">
            @else
                <i class="bi bi-box-seam" style="font-size: 2rem; margin-bottom: 10px;"></i>
            @endif
            <h4>{{ $companyShortName }}</h4>
            <small>{{ $companyFullName }}</small>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-title">Main</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>

            @permission('users.view')
            <div class="nav-section-title">User Management</div>
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i>
                <span>Users</span>
            </a>
            @endpermission

            @anypermission(['companies.view', 'products.view', 'warehouses.view', 'inventory.view', 'suppliers.view', 'customers.view'])
            <div class="nav-section-title">Inventory Management</div>

            @permission('companies.view')
            <a href="{{ route('admin.companies.index') }}" class="nav-link {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i>
                <span>Companies</span>
            </a>
            @endpermission

            @permission('products.view')
            <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i>
                <span>Products</span>
            </a>
            @endpermission

            @permission('warehouses.view')
            <a href="{{ route('admin.warehouses.index') }}" class="nav-link {{ request()->routeIs('admin.warehouses.*') ? 'active' : '' }}">
                <i class="bi bi-building-fill-gear"></i>
                <span>Warehouses</span>
            </a>
            @endpermission

            @permission('inventory.view')
            <a href="{{ route('admin.inventory.index') }}" class="nav-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
                <i class="bi bi-collection"></i>
                <span>Inventory</span>
            </a>
            @endpermission

            @permission('suppliers.view')
            <a href="{{ route('admin.suppliers.index') }}" class="nav-link {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i>
                <span>Suppliers</span>
            </a>
            @endpermission

            @permission('customers.view')
            <a href="{{ route('admin.customers.index') }}" class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Customers</span>
            </a>
            @endpermission
            @endanypermission

            @anypermission(['purchases.view', 'sales.view', 'udhar.view', 'payables.view'])
            <div class="nav-section-title">Transactions</div>

            @permission('purchases.view')
            <a href="{{ route('admin.purchases.index') }}" class="nav-link {{ request()->routeIs('admin.purchases.*') ? 'active' : '' }}">
                <i class="bi bi-cart"></i>
                <span>Purchases</span>
            </a>
            @endpermission

            @permission('sales.view')
            <a href="{{ route('admin.sales.index') }}" class="nav-link {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i>
                <span>Sales</span>
            </a>
            @endpermission

            @permission('udhar.view')
            <a href="{{ route('admin.udhar.index') }}" class="nav-link {{ request()->routeIs('admin.udhar.*') ? 'active' : '' }}">
                <i class="bi bi-credit-card"></i>
                <span>Udhar Management</span>
            </a>
            @endpermission

            @permission('payables.view')
            <a href="{{ route('admin.payables.index') }}" class="nav-link {{ request()->routeIs('admin.payables.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-check"></i>
                <span>Supplier Payables</span>
            </a>
            @endpermission
            @endanypermission

            @permission('transfers.view')
            <div class="nav-section-title">Management</div>
            <a href="{{ route('admin.stock-transfers.index') }}" class="nav-link {{ request()->routeIs('admin.stock-transfers.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-left-right"></i>
                <span>Stock Transfers</span>
            </a>
            @endpermission

            @permission('welcome-page.manage')
            <a href="{{ route('admin.welcome-page.index') }}" class="nav-link {{ request()->routeIs('admin.welcome-page.*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i>
                <span>Welcome Page Settings</span>
            </a>
            @endpermission

            {{-- Reports section disabled - routes and views not yet implemented --}}
            {{-- @permission('reports.view')
            <div class="nav-section-title">Reports</div>
            <a href="{{ route('admin.reports.sales.index') }}" class="nav-link {{ request()->routeIs('admin.reports.sales.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up"></i>
                <span>Sales Reports</span>
            </a>
            <a href="{{ route('admin.reports.inventory.index') }}" class="nav-link {{ request()->routeIs('admin.reports.inventory.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Stock Reports</span>
            </a>
            @endpermission --}}
        </nav>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <!-- Top Navbar -->
        <nav class="topbar">
            <button class="toggle-sidebar" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>

            <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm me-3" title="Go to Home Page">
                <i class="bi bi-house-fill me-1"></i>Home
            </a>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    @yield('breadcrumbs')
                </ol>
            </nav>

            <div class="topbar-right">
                <div class="dropdown">
                    <button class="btn btn-link text-dark text-decoration-none dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                        <img src="{{ Auth::user()->profile_image_url }}" 
                             alt="{{ Auth::user()->name }}" 
                             class="rounded-circle me-2" 
                             style="width: 35px; height: 35px; object-fit: cover;">
                        <span>{{ Auth::user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <div class="dropdown-header">
                                <div class="fw-bold">{{ Auth::user()->name }}</div>
                                <small class="text-muted">{{ Auth::user()->email }}</small>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person me-2"></i>Profile Settings
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Content Area -->
        <main class="content">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>There were some errors with your submission:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Page Content -->
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const sidebar = document.getElementById('sidebar');

        function toggleSidebar() {
            sidebar.classList.toggle('show');
        }

        // Restore sidebar scroll position on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedScrollPosition = sessionStorage.getItem('sidebarScrollPosition');
            if (savedScrollPosition) {
                sidebar.scrollTop = parseInt(savedScrollPosition, 10);
                sessionStorage.removeItem('sidebarScrollPosition');
            }
        });

        // Save sidebar scroll position before navigation
        document.querySelectorAll('.sidebar-nav a').forEach(function(link) {
            link.addEventListener('click', function() {
                sessionStorage.setItem('sidebarScrollPosition', sidebar.scrollTop);
            });
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>

    @stack('scripts')
</body>
</html>
