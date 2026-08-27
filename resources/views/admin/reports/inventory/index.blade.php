@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Inventory Reports</h1>
            <p class="text-muted mb-0">Choose an inventory report to view</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3"><i class="bi bi-boxes text-primary" style="font-size:3rem"></i></div>
                    <h5 class="card-title">Current Stock</h5>
                    <p class="card-text text-muted">Live inventory levels with low-stock alerts and stock value</p>
                    <a href="{{ route('admin.reports.inventory.current-stock') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-right me-1"></i>View Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3"><i class="bi bi-building text-success" style="font-size:3rem"></i></div>
                    <h5 class="card-title">Warehouse Stock</h5>
                    <p class="card-text text-muted">Compare stock levels across warehouses in a single pivot table</p>
                    <a href="{{ route('admin.reports.inventory.warehouse-stock') }}" class="btn btn-success">
                        <i class="bi bi-arrow-right me-1"></i>View Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3"><i class="bi bi-arrow-left-right text-info" style="font-size:3rem"></i></div>
                    <h5 class="card-title">Stock Movements</h5>
                    <p class="card-text text-muted">Full audit trail of every inventory movement — in, out, transfers, adjustments</p>
                    <a href="{{ route('admin.reports.inventory.stock-movements') }}" class="btn btn-info">
                        <i class="bi bi-arrow-right me-1"></i>View Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
