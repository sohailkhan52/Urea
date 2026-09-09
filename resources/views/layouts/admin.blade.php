{{-- @noinspection PhpUndefinedClassInspection --}}
{{-- @noinspection PhpUndefinedClassInspection --}}
<!DOCTYPE html>
@php use Illuminate\Support\Facades\Auth; @endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ \App\Models\Company::first()?->name ?? 'DeraNexa' }}</title>

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    @stack('styles')

    {{-- WebSocket Broadcasting Setup --}}
    @php
        $broadcastDriver = env('BROADCAST_DRIVER', 'log');
    @endphp

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
            overflow-x: auto;
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
            overflow-x: hidden;
            transition: all 0.3s;
            z-index: 1040;
        }

        /* Mobile backdrop overlay */
        @media (max-width: 768px) {
            .sidebar-backdrop {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1039;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s, visibility 0.3s;
                pointer-events: none;
            }

            .sidebar.show ~ .sidebar-backdrop {
                display: block;
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
            }
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
            flex-shrink: 0;
        }

        .nav-link-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            gap: 8px;
            min-width: 0;
            overflow: hidden;
        }

        .nav-link-text-en {
            font-size: 0.95rem;
            line-height: 1.2;
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .nav-link-text-ur {
            font-size: 0.75rem;
            color: #b0b8c1;
            line-height: 1.2;
            text-align: right;
            flex-shrink: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Dropdown Menu Styles */
        .nav-dropdown {
            position: relative;
        }

        .nav-dropdown .dropdown-toggle {
            cursor: pointer;
        }

        .nav-dropdown-indicator {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.75);
            transition: transform 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 8px;
        }

        .nav-dropdown .dropdown-toggle[aria-expanded="true"] .nav-dropdown-indicator {
            transform: rotate(180deg);
        }

        .sidebar-nav .dropdown-menu {
            border: none;
            background: rgba(0, 0, 0, 0.3);
            padding: 5px 0;
            position: relative !important;
            float: none !important;
            width: 100%;
            box-shadow: none !important;
            border-radius: 0;
            margin: 0 !important;
            display: none;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .nav-dropdown.open .dropdown-menu,
        .nav-dropdown .dropdown-toggle[aria-expanded="true"] ~ .dropdown-menu {
            display: block;
            max-height: 500px;
        }

        .sidebar-nav .dropdown-menu .dropdown-item {
            color: #ecf0f1;
            padding: 10px 20px 10px 50px;
            font-size: 0.9rem;
            transition: all 0.2s;
            display: block;
            width: 100%;
            text-align: left;
        }

        .sidebar-nav .dropdown-menu .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .sidebar-nav .dropdown-menu .dropdown-item.active {
            background: rgba(52, 152, 219, 0.3);
            color: #3498db;
        }

        /* Main Content Area */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
            overflow-x: auto;
            width: calc(100% - var(--sidebar-width));
            box-sizing: border-box;
            position: relative;
        }

        @media (max-width: 768px) {
            .main-wrapper {
                margin-left: 0;
                width: 100%;
                overflow-x: hidden;
            }
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
            gap: 15px;
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

        .app-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 2000;
            width: min(420px, calc(100vw - 40px));
            margin: 0;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.16);
        }

        @media (max-width: 576px) {
            .app-toast {
                top: 10px;
                right: 10px;
                width: calc(100vw - 20px);
            }
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
            :root {
                --sidebar-width: 260px;
                --topbar-height: 60px;
            }

            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                width: var(--sidebar-width);
                margin-left: calc(-1 * var(--sidebar-width));
                z-index: 1041;
            }

            .sidebar.show {
                margin-left: 0;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            }

            .main-wrapper {
                margin-left: 0;
                width: 100%;
            }

            .topbar {
                margin-left: 0;
            }

            .toggle-sidebar {
                display: block;
            }

            .sidebar-brand h4 {
                font-size: 1rem;
            }

            .sidebar-brand small {
                font-size: 0.65rem;
            }

            .nav-section-title {
                padding: 10px 15px 5px;
                font-size: 0.65rem;
            }

            .sidebar-nav .nav-link {
                padding: 10px 15px;
                font-size: 0.9rem;
            }

            .sidebar-nav .dropdown-menu .dropdown-item {
                padding: 8px 15px 8px 40px;
                font-size: 0.85rem;
            }

            .page-header {
                padding: 15px 15px;
                margin: -15px -15px 15px;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .content {
                padding: 15px;
            }

            .card-header {
                padding: 10px 15px;
                font-size: 0.9rem;
            }

            .table {
                font-size: 0.85rem;
            }

            .btn-sm {
                padding: 0.4rem 0.6rem;
                font-size: 0.8rem;
            }
        }

        /* Extra small devices (Mobile S - up to 375px) */
        @media (max-width: 375px) {
            :root {
                --sidebar-width: 240px;
                --topbar-height: 50px;
            }

            .topbar {
                height: var(--topbar-height);
                padding: 0 12px;
                gap: 10px;
            }

            .sidebar-brand h4 {
                font-size: 0.9rem;
            }

            .sidebar-brand small {
                display: none;
            }

            .nav-section-title {
                display: none;
            }

            .sidebar-nav .nav-link {
                padding: 8px 12px;
                font-size: 0.8rem;
            }

            .sidebar-nav .nav-link i {
                margin-right: 8px;
            }

            .sidebar-nav .nav-link-text-ur {
                display: none;
            }

            .page-header {
                padding: 12px 12px;
                margin: -12px -12px 12px;
            }

            .page-header h1 {
                font-size: 1.25rem;
            }

            .page-header p {
                display: none;
            }

            .content {
                padding: 12px;
            }

            .card {
                margin-bottom: 15px;
            }

            .card-header {
                padding: 8px 12px;
                font-size: 0.8rem;
            }

            .btn-sm {
                padding: 0.35rem 0.5rem;
                font-size: 0.75rem;
            }

            .d-flex.gap-2 {
                gap: 0.5rem !important;
            }

            .table {
                font-size: 0.75rem;
            }

            .table thead {
                font-size: 0.7rem;
            }

            .form-control, .form-select {
                padding: 0.4rem 0.5rem;
                font-size: 0.85rem;
            }

            .topbar-right {
                gap: 8px;
            }

            .breadcrumb {
                display: none;
            }
        }

        /* Small devices (Mobile M - 375px to 425px) */
        @media (min-width: 376px) and (max-width: 425px) {
            :root {
                --sidebar-width: 250px;
            }

            .sidebar-brand small {
                font-size: 0.7rem;
            }

            .sidebar-nav .nav-link-text-ur {
                font-size: 0.65rem;
            }

            .page-header h1 {
                font-size: 1.35rem;
            }

            .table {
                font-size: 0.8rem;
            }
        }

        /* Medium devices (Tablet - 426px to 768px) */
        @media (min-width: 426px) and (max-width: 768px) {
            :root {
                --sidebar-width: 260px;
            }

            .sidebar-nav .nav-link {
                padding: 11px 18px;
                font-size: 0.92rem;
            }

            .sidebar-nav .nav-link-text-ur {
                font-size: 0.7rem;
            }

            .page-header h1 {
                font-size: 1.6rem;
            }

            .table {
                font-size: 0.87rem;
            }

            .d-flex.gap-2 {
                gap: 0.75rem !important;
            }
        }

        /* Large devices and above (Desktop - 769px+) */
        @media (min-width: 769px) {
            :root {
                --sidebar-width: 260px;
            }

            .sidebar {
                margin-left: 0;
            }

            .main-wrapper {
                margin-left: var(--sidebar-width);
                width: calc(100% - var(--sidebar-width));
            }

            .toggle-sidebar {
                display: none;
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
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            display: block;
            width: 100%;
        }

        @media (max-width: 768px) {
            .table-responsive {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                white-space: nowrap;
            }

            .table-responsive table {
                min-width: 100%;
                margin-bottom: 0;
            }
        }

        .table td .dropdown {
            position: static;
        }

        .table td .dropdown-menu {
            position: absolute;
            z-index: 1050;
        }

        /* Modal Styles - Keep Bootstrap defaults */
        .modal-content {
            border: 1px solid rgba(0, 0, 0, 0.15);
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .modal-header, .modal-footer {
            border-color: #e3e6f0;
        }

        .modal-body {
            padding: 20px;
        }

        /* Keep Bootstrap's default modal stacking behavior. */
        .modal-backdrop {
            backdrop-filter: none !important;
            background-color: rgba(0, 0, 0, 0.3) !important;
        }

        /* Ensure body can scroll when modal is closed */
        body.modal-open {
            overflow: hidden;
            padding-right: var(--bs-scrollbar-width);
        }

        body:not(.modal-open) {
            overflow: auto;
            padding-right: 0;
        }

        /* Mobile Select Dropdown Fix */
        @media (max-width: 768px) {
            select {
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            /* When size attribute is set, show as dropdown list */
            select[size] {
                height: auto;
                max-height: 60vh;
                overflow-y: auto;
                overflow-x: hidden;
                border: 1px solid #dee2e6;
                border-radius: 0.375rem;
                padding: 0.375rem 0.75rem;
                background-color: #fff;
                display: block;
                position: relative;
                z-index: 9999;
            }

            /* Style the options in expanded state */
            select[size] option {
                padding: 0.5rem 0.75rem;
                white-space: normal;
                word-wrap: break-word;
            }

            select[size] option:hover,
            select[size] option:focus {
                background: #0d6efd;
                color: white;
            }

            /* Ensure form containers don't overflow */
            .form-control,
            .form-select,
            input[type="text"],
            input[type="search"],
            input[type="date"],
            textarea {
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            /* Fix container padding issues */
            .card {
                overflow: hidden;
            }

            /* Ensure row doesn't create horizontal scroll */
            .row {
                overflow-x: hidden;
            }

            .col-md-1, .col-md-2, .col-md-3, .col-md-4, .col-md-6, .col-md-12 {
                overflow-x: hidden;
            }
        }

        @media print {
            html,
            body,
            body * {
                color: #000000 !important;
                text-shadow: none !important;
                filter: none !important;
            }

            html,
            body,
            body * {
                background: #ffffff !important;
                border-color: #000000 !important;
                box-shadow: none !important;
            }

            .no-print,
            .no-print * {
                display: none !important;
            }

            a,
            a:visited {
                color: #000000 !important;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            @php
                $company = \App\Models\Company::first();
                $companyName = $company?->name ?? 'DeraNex';
                $companyLogo = $company?->logo ? asset('storage/' . $company->logo) : null;
            @endphp
            @if($companyLogo)
                <img src="{{ $companyLogo }}" alt="Logo" style="max-height: 40px; width: auto; margin-bottom: 10px;">
            @else
                <i class="bi bi-box-seam" style="font-size: 2rem; margin-bottom: 10px;"></i>
            @endif
            <h4>{{ $companyName }}</h4>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-title">Main</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <div class="nav-link-wrapper">
                    <span class="nav-link-text-en">Dashboard</span>
                    <span class="nav-link-text-ur">ڈیش بورڈ</span>
                </div>
            </a>

            {{-- PHASE 1a REMOVED: User Management section --}}

            {{-- PHASE 1a REMOVED: Inventory Management section (Companies, Categories, Products, Warehouses, Inventory) --}}
            {{-- Models retained for Sales/Purchases/Reports; UI management removed --}}

            {{-- KEEP: Supplier Management (required by Purchases/Payables) --}}
            {{-- Suppliers removed in Phase 2 --}}

            @anypermission(['purchases.view', 'sales.view', 'udhar.view', 'payables.view'])
            <div class="nav-section-title">Transactions</div>

            @if(auth()->user()->isSuperAdmin())
            <div class="nav-dropdown">
                <a href="#" class="nav-link dropdown-toggle {{ request()->routeIs('admin.purchases.*') ? 'active' : '' }}" aria-expanded="false">
                    <i class="bi bi-cart"></i>
                    <div class="nav-link-wrapper">
                        <span class="nav-link-text-en">Purchases</span>
                        <span class="nav-link-text-ur">خریداری</span>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark">
                    <li>
                        <a href="{{ route('admin.purchases.index') }}" class="dropdown-item {{ request()->routeIs('admin.purchases.index') ? 'active' : '' }}">
                            <i class="bi bi-list me-2"></i> View All
                        </a>
                    </li>
                    @can('purchases.create')
                    <li>
                        <a href="{{ route('admin.purchases.create') }}" class="dropdown-item {{ request()->routeIs('admin.purchases.create') ? 'active' : '' }}">
                            <i class="bi bi-plus-circle me-2"></i> Add Purchase
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
            @endif

            @permission('purchases.view')
            <div class="nav-dropdown">
                <a href="#" class="nav-link dropdown-toggle {{ request()->routeIs('admin.purchase-returns.*') ? 'active' : '' }}" aria-expanded="false">
                    <i class="bi bi-arrow-return-left"></i>
                    <div class="nav-link-wrapper">
                        <span class="nav-link-text-en">Purchase Returns</span>
                        <span class="nav-link-text-ur">خریداری واپسی</span>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark">
                    <li>
                        <a href="{{ route('admin.purchase-returns.index') }}" class="dropdown-item">
                            <i class="bi bi-list me-2"></i> View All Returns
                        </a>
                    </li>
                    @can('purchases.create')
                    <li>
                        <a href="{{ route('admin.purchase-returns.create') }}" class="dropdown-item">
                            <i class="bi bi-plus-circle me-2"></i> Create Return
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
            @endpermission

            @permission('purchases.view')

            @endpermission

            @permission('sales.view')
            <div class="nav-dropdown">
                <a href="#" class="nav-link dropdown-toggle {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}" aria-expanded="false">
                    <i class="bi bi-receipt"></i>
                    <div class="nav-link-wrapper">
                        <span class="nav-link-text-en">Sales</span>
                        <span class="nav-link-text-ur">فروخت</span>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark">
                    <li>
                        <a href="{{ route('admin.sales.index') }}" class="dropdown-item {{ request()->routeIs('admin.sales.index') ? 'active' : '' }}">
                            <i class="bi bi-list me-2"></i> View All
                        </a>
                    </li>
                    @can('sales.create')
                    <li>
                        <a href="{{ route('admin.sales.create') }}" class="dropdown-item {{ request()->routeIs('admin.sales.create') ? 'active' : '' }}">
                            <i class="bi bi-plus-circle me-2"></i> Add Sale
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
            @endpermission

            @permission('sales.view')
            <div class="nav-dropdown">
                <a href="#" class="nav-link dropdown-toggle {{ request()->routeIs('admin.sale-returns.*') ? 'active' : '' }}" aria-expanded="false">
                    <i class="bi bi-arrow-return-left"></i>
                     <span class="nav-link-text-en">Sale Return</span>
                        <span class="nav-link-text-ur">فروخت واپسی</span>
                    <span class="nav-link-text-ur"></span>
      </a>
                <ul class="dropdown-menu dropdown-menu-dark">
                    <li>
                        <a href="{{ route('admin.sale-returns.index') }}" class="dropdown-item {{ request()->routeIs('admin.sale-returns.index') ? 'active' : '' }}">
                            <i class="bi bi-list me-2"></i> View All Returns
                        </a>
                    </li>
                    @can('sales.create')
                    <li>
                        <a href="{{ route('admin.sale-returns.create') }}" class="dropdown-item {{ request()->routeIs('admin.sale-returns.create') ? 'active' : '' }}">
                            <i class="bi bi-plus-circle me-2"></i> Create Return
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
            @endpermission

            <a href="{{ route('admin.supplier-payables.index') }}" class="nav-link {{ request()->routeIs('admin.supplier-payables.*') ? 'active' : '' }}">
                <i class="bi bi-wallet2"></i>
                <div class="nav-link-wrapper">
                    <span class="nav-link-text-en">Supplier Payables</span>
                    <span class="nav-link-text-ur">سپلائر ادائیگیاں</span>
                </div>
            </a>

            <a href="{{ route('admin.udhar.index') }}" class="nav-link {{ request()->routeIs('admin.udhar.*') ? 'active' : '' }}">
                <i class="bi bi-credit-card"></i>
                <div class="nav-link-wrapper">
                    <span class="nav-link-text-en">Udhar Management</span>
                    <span class="nav-link-text-ur">اُدھار</span>
                </div>
            </a>
            @endanypermission

            {{-- Reports Section --}}
            @anypermission(['sales.view', 'purchases.view'])
            <div class="nav-section-title">Reports</div>

            @permission('sales.view')
            <div class="nav-dropdown">
                <a href="#" class="nav-link dropdown-toggle {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" aria-expanded="false">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <div class="nav-link-wrapper">
                        <span class="nav-link-text-en">Reports</span>
                        <span class="nav-link-text-ur">رپورٹس</span>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark">
                    @can('sales.view')
                    <li>
                        <a href="{{ route('admin.reports.sales.index') }}" class="dropdown-item {{ request()->routeIs('admin.reports.sales.*') ? 'active' : '' }}">
                            <i class="bi bi-receipt me-2"></i> Sale Report
                        </a>
                    </li>
                    @endcan
                    @can('purchases.view')
                    <li>
                        <a href="{{ route('admin.reports.purchases.index') }}" class="dropdown-item {{ request()->routeIs('admin.reports.purchases.*') ? 'active' : '' }}">
                            <i class="bi bi-cart me-2"></i> Purchase Report
                        </a>
                    </li>
                    @endcan
                    @can('sales.view')
                    <li>
                        <a href="{{ route('admin.reports.profit-loss.index') }}" class="dropdown-item {{ request()->routeIs('admin.reports.profit-loss.*') ? 'active' : '' }}">
                            <i class="bi bi-graph-up-arrow me-2"></i> Profit & Loss
                        </a>
                    </li>
                    @endcan
                    @can('products.view')
                    <li>
                        <a href="{{ route('admin.reports.products.index') }}" class="dropdown-item {{ request()->routeIs('admin.reports.products.*') ? 'active' : '' }}">
                            <i class="bi bi-box-seam me-2"></i> Products
                        </a>
                    </li>
                    @endcan
                    @if(auth()->user()->isSuperAdmin())
                    <li>
                        <a href="{{ route('admin.families.index') }}" class="dropdown-item {{ request()->routeIs('admin.families.*') ? 'active' : '' }}">
                            <i class="bi bi-people me-2"></i> Families
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
            @endpermission
            @endanypermission
        </nav>
    </aside>

    <!-- Sidebar Backdrop (Mobile overlay) -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <!-- Top Navbar -->
        <nav class="topbar">
            <button class="toggle-sidebar" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>

            <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm me-2" title="Go to Home Page">
                <i class="bi bi-house-fill me-1"></i>Home
            </a>

            {{-- Back Button (Hidden on Dashboard and Welcome) --}}
            @php
                $currentRoute = Route::currentRouteName();
                $showBackButton = !in_array($currentRoute, ['admin.dashboard', 'dashboard', 'welcome', 'home']);
            @endphp
            @if($showBackButton)
            <button onclick="window.history.back()" class="btn btn-outline-secondary btn-sm me-3" title="Go to Previous Page">
                <i class="bi bi-arrow-left me-1"></i>Back
            </button>
            @endif

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    @yield('breadcrumbs')
                </ol>
            </nav>

            <div class="topbar-right">
                <div class="dropdown">
                    <button class="btn btn-link text-dark text-decoration-none dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                        {{-- @noinspection PhpUndefinedClassInspection --}}
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
                <div class="alert alert-success alert-dismissible fade show app-toast" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show app-toast" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show app-toast" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show app-toast" role="alert">
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- WebSocket Broadcasting Libraries --}}
    @if($broadcastDriver !== 'log' && $broadcastDriver !== 'null')
        @if($broadcastDriver === 'websocket')
            {{-- Development: Laravel WebSockets --}}
            <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.0.0/dist/web/pusher.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js"></script>

            {{-- @noinspection PhpUndefinedClassInspection --}}
            <script>
                window.Echo = new Echo({
                    broadcaster: 'pusher',
                    key: 'null-key',
                    wsHost: window.location.hostname,
                    wsPort: {{ env('LARAVEL_WEBSOCKETS_PORT', 6001) }},
                    wssPort: {{ env('LARAVEL_WEBSOCKETS_PORT', 6001) }},
                    forceTLS: false,
                    encrypted: false,
                    enabledTransports: ['ws', 'wss'],
                });
            </script>
        @elseif($broadcastDriver === 'pusher')
            {{-- Production: Pusher Service --}}
            <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.0.0/dist/web/pusher.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js"></script>

            {{-- @noinspection PhpUndefinedClassInspection --}}
            <script>
                window.Echo = new Echo({
                    broadcaster: 'pusher',
                    key: '{{ config('broadcasting.connections.pusher.key') }}',
                    cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
                    forceTLS: true,
                    auth: {
                        headers: {
                            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    }
                });
            </script>
        @endif
    @endif

    <script>
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        function toggleSidebar() {
            sidebar.classList.toggle('show');
        }

        // Close sidebar when clicking on backdrop
        if (backdrop) {
            backdrop.addEventListener('click', function() {
                sidebar.classList.remove('show');
            });
        }

        // Close sidebar when clicking on a nav link (mobile)
        document.querySelectorAll('.sidebar-nav a:not([aria-expanded])').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('show');
                }
            });
        });

        // Close sidebar when window resizes to desktop size
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
            }
        });

        // Close sidebar when clicking on main-wrapper (mobile)
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768) {
                // Don't close sidebar if clicking inside a modal
                const isClickInsideModal = event.target.closest('.modal');
                if (isClickInsideModal) {
                    return;
                }

                const isClickInsideSidebar = sidebar.contains(event.target);
                const isToggleButton = event.target.closest('.toggle-sidebar');
                
                if (!isClickInsideSidebar && !isToggleButton && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            }
        });

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

        // Handle dropdown toggles in sidebar navigation
        document.querySelectorAll('.nav-dropdown .dropdown-toggle').forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();

                const navDropdown = toggle.closest('.nav-dropdown');
                const isExpanded = toggle.getAttribute('aria-expanded') === 'true';

                document.querySelectorAll('.nav-dropdown .dropdown-toggle').forEach(function(otherToggle) {
                    if (otherToggle !== toggle) {
                        otherToggle.setAttribute('aria-expanded', 'false');
                        otherToggle.closest('.nav-dropdown')?.classList.remove('open');
                    }
                });

                const nextState = !isExpanded;
                toggle.setAttribute('aria-expanded', String(nextState));
                navDropdown?.classList.toggle('open', nextState);
            });
        });

        document.querySelectorAll('.nav-dropdown').forEach(function(navDropdown) {
            const activeItem = navDropdown.querySelector('.nav-link.active, .dropdown-item.active');
            const toggle = navDropdown.querySelector('.dropdown-toggle');

            if (activeItem && toggle) {
                toggle.setAttribute('aria-expanded', 'true');
                navDropdown.classList.add('open');
            }
        });

        // Mobile Select Dropdown Fix - Close on selection
        document.addEventListener('DOMContentLoaded', function() {
            const isMobileView = window.innerWidth <= 768;
            
            if (isMobileView) {
                const selectElements = document.querySelectorAll('select');
                selectElements.forEach((select, index) => {
                    // Create wrapper for custom dropdown
                    const wrapper = document.createElement('div');
                    wrapper.className = 'mobile-select-wrapper';
                    wrapper.style.position = 'relative';
                    select.parentNode.insertBefore(wrapper, select);
                    wrapper.appendChild(select);
                    
                    // Hide original select visually but keep it for form submission
                    select.style.display = 'none';
                    
                    // Create custom dropdown container
                    const customDropdown = document.createElement('div');
                    customDropdown.className = 'mobile-custom-select';
                    customDropdown.style.cssText = 'position: relative; width: 100%;';
                    
                    // Create display button
                    const displayBtn = document.createElement('button');
                    displayBtn.className = 'form-control mobile-select-display';
                    displayBtn.type = 'button';
                    displayBtn.textContent = select.options[select.selectedIndex].text;
                    displayBtn.style.cssText = 'width: 100%; text-align: left; padding: 0.375rem 0.75rem; padding-right: 2.5rem; border: 1px solid #dee2e6; border-radius: 0.375rem; background: white; cursor: pointer; position: relative; background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'%3e%3cpolyline points=\'6 9 12 15 18 9\'%3e%3c/polyline%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1.15em;';
                    
                    // Create dropdown list container
                    const listContainer = document.createElement('div');
                    listContainer.className = 'mobile-select-list-container';
                    
                    // Function to position dropdown
                    const positionDropdown = () => {
                        const btnRect = displayBtn.getBoundingClientRect();
                        listContainer.style.position = 'fixed';
                        listContainer.style.top = (btnRect.bottom + 2) + 'px';
                        listContainer.style.left = btnRect.left + 'px';
                        listContainer.style.width = btnRect.width + 'px';
                        listContainer.style.maxHeight = '60vh';
                        listContainer.style.overflow = 'auto';
                        listContainer.style.zIndex = '10000';
                        listContainer.style.background = 'white';
                        listContainer.style.border = '1px solid #dee2e6';
                        listContainer.style.borderRadius = '0 0 0.375rem 0.375rem';
                        listContainer.style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
                        listContainer.style.display = 'none';
                    };
                    positionDropdown();
                    
                    // Create search input
                    const searchInput = document.createElement('input');
                    searchInput.type = 'text';
                    searchInput.className = 'mobile-select-search';
                    searchInput.placeholder = 'Search...';
                    searchInput.style.cssText = 'width: 100%; padding: 0.5rem 0.75rem; border: none; border-bottom: 1px solid #dee2e6; box-sizing: border-box; font-size: 14px;';
                    listContainer.appendChild(searchInput);
                    
                    // Create options list
                    const optionsList = document.createElement('div');
                    optionsList.className = 'mobile-select-options';
                    
                    // Populate options
                    for (let i = 0; i < select.options.length; i++) {
                        const option = select.options[i];
                        const optionItem = document.createElement('div');
                        optionItem.className = 'mobile-select-option';
                        optionItem.textContent = option.text;
                        optionItem.dataset.value = option.value;
                        optionItem.dataset.index = i;
                        optionItem.style.cssText = 'padding: 0.5rem 0.75rem; cursor: pointer; border-bottom: 1px solid #f0f0f0; transition: background-color 0.2s;';
                        
                        if (i === select.selectedIndex) {
                            optionItem.style.backgroundColor = '#0d6efd';
                            optionItem.style.color = 'white';
                        }
                        
                        optionItem.addEventListener('mouseenter', function() {
                            if (this.style.display !== 'none') {
                                this.style.backgroundColor = '#e7f1ff';
                                this.style.color = '';
                            }
                        });
                        optionItem.addEventListener('mouseleave', function() {
                            if (this.dataset.index !== select.selectedIndex.toString()) {
                                this.style.backgroundColor = '';
                                this.style.color = '';
                            }
                        });
                        
                        optionItem.addEventListener('click', function() {
                            select.selectedIndex = parseInt(this.dataset.index);
                            select.dispatchEvent(new Event('change', { bubbles: true }));
                            displayBtn.textContent = this.textContent;
                            listContainer.style.display = 'none';
                            searchInput.value = '';
                            
                            document.querySelectorAll('.mobile-select-option').forEach(opt => {
                                if (opt.dataset.index === this.dataset.index) {
                                    opt.style.backgroundColor = '#0d6efd';
                                    opt.style.color = 'white';
                                } else {
                                    opt.style.backgroundColor = '';
                                    opt.style.color = '';
                                }
                            });
                        });
                        
                        optionsList.appendChild(optionItem);
                    }
                    
                    listContainer.appendChild(optionsList);
                    customDropdown.appendChild(displayBtn);
                    customDropdown.appendChild(listContainer);
                    wrapper.appendChild(customDropdown);
                    
                    displayBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (listContainer.style.display === 'none') {
                            positionDropdown();
                            listContainer.style.display = 'block';
                            searchInput.focus();
                        } else {
                            listContainer.style.display = 'none';
                        }
                    });
                    
                    searchInput.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase().trim();
                        const options = optionsList.querySelectorAll('.mobile-select-option');
                        let visibleCount = 0;
                        
                        options.forEach(option => {
                            const text = option.textContent.toLowerCase();
                            if (searchTerm === '' || text.includes(searchTerm)) {
                                option.style.display = 'block';
                                visibleCount++;
                            } else {
                                option.style.display = 'none';
                            }
                        });
                        
                        // Show "no results" message if needed
                        if (visibleCount === 0 && searchTerm !== '') {
                            let noResultsMsg = optionsList.querySelector('.no-results-msg');
                            if (!noResultsMsg) {
                                noResultsMsg = document.createElement('div');
                                noResultsMsg.className = 'no-results-msg';
                                noResultsMsg.textContent = 'No options found';
                                noResultsMsg.style.cssText = 'padding: 1rem 0.75rem; text-align: center; color: #999; font-size: 14px;';
                                optionsList.appendChild(noResultsMsg);
                            }
                        } else {
                            const noResultsMsg = optionsList.querySelector('.no-results-msg');
                            if (noResultsMsg) {
                                noResultsMsg.remove();
                            }
                        }
                    });
                    
                    document.addEventListener('click', function(e) {
                        if (!wrapper.contains(e.target)) {
                            listContainer.style.display = 'none';
                            searchInput.value = '';
                            optionsList.querySelectorAll('.mobile-select-option').forEach(opt => {
                                opt.style.display = 'block';
                            });
                            const noResultsMsg = optionsList.querySelector('.no-results-msg');
                            if (noResultsMsg) {
                                noResultsMsg.remove();
                            }
                        }
                    });
                });
            }
        });

    </script>

    @stack('modals')
    @stack('scripts')
</body>
</html>
