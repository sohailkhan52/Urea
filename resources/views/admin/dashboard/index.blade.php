@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="mb-4">
        <h1 class="h3 mb-0">Dashboard</h1>
        <p class="text-muted small">Welcome back! Here's your business overview.</p>
    </div>

    {{-- Management Quick Links --}}
    <div class="row mb-5">
        <!-- Sales Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('admin.sales.index') }}" class="text-decoration-none">
                <div class="card management-card h-100 border-0 shadow-sm" style="cursor: pointer; transition: all 0.3s ease; border-left: 4px solid #e3165b !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small mb-2">Sales</div>
                                <div class="h4 mb-0 fw-bold text-primary">
                                    {{ $totalSales ?? 0 }}
                                </div>
                            </div>
                            <div class="text-primary" style="font-size: 3rem; opacity: 0.15;">
                                <i class="bi bi-bag-check"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <i class="bi bi-arrow-right me-1"></i>View All Sales
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Purchases Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('admin.purchases.index') }}" class="text-decoration-none">
                <div class="card management-card h-100 border-0 shadow-sm" style="cursor: pointer; transition: all 0.3s ease; border-left: 4px solid #198754 !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small mb-2">Purchases</div>
                                <div class="h4 mb-0 fw-bold text-success">
                                    {{ $totalPurchases ?? 0 }}
                                </div>
                            </div>
                            <div class="text-success" style="font-size: 3rem; opacity: 0.15;">
                                <i class="bi bi-cart-plus"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <i class="bi bi-arrow-right me-1"></i>View All Purchases
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Udhar (Credit) Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('admin.udhar.index') }}" class="text-decoration-none">
                <div class="card management-card h-100 border-0 shadow-sm" style="cursor: pointer; transition: all 0.3s ease; border-left: 4px solid #ffc107 !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small mb-2">Udhar (Credit)</div>
                                <div class="h4 mb-0 fw-bold text-warning">
                                    PKR {{ number_format($totalUdhar ?? 0, 0) }}
                                </div>
                            </div>
                            <div class="text-warning" style="font-size: 3rem; opacity: 0.15;">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <i class="bi bi-arrow-right me-1"></i>View Udhar Details
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Payables Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('admin.supplier-payables.index') }}" class="text-decoration-none">
                <div class="card management-card h-100 border-0 shadow-sm" style="cursor: pointer; transition: all 0.3s ease; border-left: 4px solid #dc3545 !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small mb-2">Payables</div>
                                <div class="h4 mb-0 fw-bold text-danger">
                                    PKR {{ number_format($totalPayables ?? 0, 0) }}
                                </div>
                            </div>
                            <div class="text-danger" style="font-size: 3rem; opacity: 0.15;">
                                <i class="bi bi-exclamation-circle"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <i class="bi bi-arrow-right me-1"></i>View Payables
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <style>
        .management-card {
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .management-card:hover {
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
            transform: translateY(-3px);
        }

        .management-card .card-body {
            padding: 1.5rem;
        }
    </style>

    {{-- Today's Statistics --}}
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Today's Sales</small>
                            <h5 class="mb-0">{{ number_format($todayStats['total_sales'], 2) }}</h5>
                        </div>
                        <i class="bi bi-cart-check" style="font-size: 2rem; color: #e3165b;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Today's Purchases</small>
                            <h5 class="mb-0">{{ number_format($todayStats['total_purchases'], 2) }}</h5>
                        </div>
                        <i class="bi bi-bag-check" style="font-size: 2rem; color: #198754;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Payments Received</small>
                            <h5 class="mb-0">{{ number_format($todayStats['payments_received'], 2) }}</h5>
                        </div>
                        <i class="bi bi-cash-coin" style="font-size: 2rem; color: #0d6efd;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Items Sold</small>
                            <h5 class="mb-0">{{ number_format($todayStats['items_sold'], 0) }}</h5>
                        </div>
                        <i class="bi bi-box" style="font-size: 2rem; color: #ffc107;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Row --}}
    <div class="row mb-4">
        {{-- Charts Column --}}
        <div class="col-lg-8">
            {{-- Daily Sales Chart --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Daily Sales (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="dailySalesChart" height="80"></canvas>
                </div>
            </div>

            {{-- Monthly Sales Chart --}}
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Monthly Sales (Last 12 Months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlySalesChart" height="80"></canvas>
                </div>
            </div>
        </div>

        {{-- Sidebar Column --}}
        <div class="col-lg-4">
            {{-- Inventory Stats --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-boxes me-2"></i> Inventory Status</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Total Products</small>
                            <strong>{{ $inventoryStats['total_products'] }}</strong>
                        </div>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Total Stock Units</small>
                            <strong>{{ number_format($inventoryStats['total_stock_units'], 0) }}</strong>
                        </div>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Low Stock Items</small>
                            <span class="badge bg-warning">{{ $inventoryStats['low_stock_count'] }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Out of Stock</small>
                            <span class="badge bg-danger">{{ $inventoryStats['out_of_stock_count'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Financial Summary --}}
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-calculator me-2"></i> Financial Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block">Outstanding Receivables</small>
                        <h6 class="mb-0 text-danger">{{ number_format($financialSummary['total_receivables'], 2) }}</h6>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block">Outstanding Payables</small>
                        <h6 class="mb-0 text-warning">{{ number_format($financialSummary['total_payables'], 2) }}</h6>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block">Inventory Value</small>
                        <h6 class="mb-0 text-success">{{ number_format($financialSummary['inventory_value'], 2) }}</h6>
                    </div>
                    <div>
                        <small class="text-muted d-block">Unpaid Invoices</small>
                        <h6 class="mb-0">{{ $financialSummary['total_unpaid_sales'] }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Statistics Row --}}
    <div class="row mb-4">
        {{-- Sales Summary --}}
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i> Sales Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block">Total Sales</small>
                        <h6 class="mb-0">{{ number_format($salesSummary['total_sales'], 2) }}</h6>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block">Total Paid</small>
                        <h6 class="mb-0 text-success">{{ number_format($salesSummary['total_paid'], 2) }}</h6>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block">Outstanding</small>
                        <h6 class="mb-0 text-danger">{{ number_format($salesSummary['total_outstanding'], 2) }}</h6>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block">Total Invoices</small>
                        <h6 class="mb-0">{{ $salesSummary['sale_count'] }}</h6>
                    </div>
                    <div>
                        <small class="text-muted d-block">Unique Customers</small>
                        <h6 class="mb-0">{{ $salesSummary['unique_customers'] }}</h6>
                    </div>
                </div>
            </div>
        </div>

        {{-- Purchase Summary --}}
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-graph-down me-2"></i> Purchase Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block">Total Purchases</small>
                        <h6 class="mb-0">{{ number_format($purchaseSummary['total_purchases'], 2) }}</h6>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block">Total Paid</small>
                        <h6 class="mb-0 text-success">{{ number_format($purchaseSummary['total_paid'], 2) }}</h6>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block">Outstanding</small>
                        <h6 class="mb-0 text-danger">{{ number_format($purchaseSummary['total_outstanding'], 2) }}</h6>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block">Total Purchase Orders</small>
                        <h6 class="mb-0">{{ $purchaseSummary['purchase_count'] }}</h6>
                    </div>
                    <div>
                        <small class="text-muted d-block">Active Suppliers</small>
                        <h6 class="mb-0">{{ $purchaseSummary['unique_suppliers'] }}</h6>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Products --}}
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-star me-2"></i> Top 5 Products</h5>
                </div>
                <div class="card-body">
                    @forelse($topProducts->take(5) as $product)
                    <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <small class="text-muted d-block">{{ $product->name }}</small>
                        <small class="text-muted">{{ number_format($product->total_revenue, 2) }} PKR</small>
                    </div>
                    @empty
                    <p class="text-muted small mb-0">No sales yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Charts for Sales by Warehouse and Customers --}}
    <div class="row mb-4">
        {{-- Sales by Warehouse --}}
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-building me-2"></i> Sales by Warehouse</h5>
                </div>
                <div class="card-body">
                    <canvas id="warehouseSalesChart" height="80"></canvas>
                </div>
            </div>
        </div>

        {{-- Top Customers --}}
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i> Top Customers</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($topCustomers as $customer)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <small class="fw-bold d-block">{{ $customer->name }}</small>
                            <small class="text-muted">{{ $customer->customer_type }}</small>
                        </div>
                        <div class="text-end">
                            <small class="d-block fw-bold">{{ number_format($customer->total_sales, 0) }}</small>
                            <small class="text-muted">Sales</small>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted small mb-0">No customers yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Low Stock Alert --}}
    @if($lowStockItems->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i> Low Stock Alert</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Warehouse</th>
                                    <th>Current Stock</th>
                                    <th>Minimum Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lowStockItems->take(10) as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product->name }}</strong><br>
                                        <small class="text-muted">{{ $item->product->sku }}</small>
                                    </td>
                                    <td>{{ $item->warehouse->name }}</td>
                                    <td><span class="badge bg-danger">{{ $item->quantity }}</span></td>
                                    <td>10</td> {{-- Fixed threshold since minimum_stock_level was removed --}}
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Recent Stock Movements --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i> Recent Stock Movements</h5>
                        {{-- Report routes not yet implemented --}}
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Warehouse</th>
                                    <th>Type</th>
                                    <th class="text-end">Qty In</th>
                                    <th class="text-end">Qty Out</th>
                                    <th>By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentMovements as $movement)
                                <tr>
                                    <td><small>{{ $movement->created_at->format('M d, Y') }}</small></td>
                                    <td>
                                        <strong>{{ $movement->product->name }}</strong><br>
                                        <small class="text-muted">{{ $movement->product->sku }}</small>
                                    </td>
                                    <td>{{ $movement->warehouse->name }}</td>
                                    <td><span class="badge bg-secondary">{{ str_replace('_', ' ', $movement->type) }}</span></td>
                                    <td class="text-end">{{ $movement->quantity_in > 0 ? '+' . number_format($movement->quantity_in, 2) : '—' }}</td>
                                    <td class="text-end">{{ $movement->quantity_out > 0 ? '-' . number_format($movement->quantity_out, 2) : '—' }}</td>
                                    <td><small>{{ $movement->creator->name }}</small></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <span class="text-muted">No stock movements yet</span>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Daily Sales Chart
const dailyCtx = document.getElementById('dailySalesChart').getContext('2d');
const dailySalesData = {!! $dailySalesData !!};
new Chart(dailyCtx, {
    type: 'line',
    data: {
        labels: dailySalesData.map(d => d.date),
        datasets: [{
            label: 'Sales Amount',
            data: dailySalesData.map(d => d.amount),
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4,
            fill: true,
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: function(value) { return '₨' + value.toLocaleString(); } }
            }
        }
    }
});

// Monthly Sales Chart
const monthlyCtx = document.getElementById('monthlySalesChart').getContext('2d');
const monthlySalesData = {!! $monthlySalesData !!};
new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: monthlySalesData.map(d => d.month),
        datasets: [{
            label: 'Monthly Sales',
            data: monthlySalesData.map(d => d.amount),
            backgroundColor: '#198754',
            borderColor: '#198754',
            borderWidth: 1,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: function(value) { return '₨' + value.toLocaleString(); } }
            }
        }
    }
});

// Warehouse Sales Chart
const warehouseCtx = document.getElementById('warehouseSalesChart').getContext('2d');
const warehouseSalesData = {!! json_encode($salesByWarehouse->pluck('total_sales', 'name')) !!};
new Chart(warehouseCtx, {
    type: 'doughnut',
    data: {
        labels: Object.keys(warehouseSalesData),
        datasets: [{
            data: Object.values(warehouseSalesData),
            backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#e3165b', '#17a2b8'],
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>
@endpush
@endsection
