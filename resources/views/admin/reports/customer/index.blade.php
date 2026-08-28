@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Customer Reports</h1>
            <p class="text-muted mb-0">Choose a customer report to view</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3"><i class="bi bi-cash-stack text-danger" style="font-size:3rem"></i></div>
                    <h5 class="card-title">Customer Udhar</h5>
                    <p class="card-text text-muted">View customers with unpaid balances</p>
                    <a href="{{ route('admin.reports.customer.outstanding') }}" class="btn btn-danger">
                        <i class="bi bi-arrow-right me-1"></i>View Report
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3"><i class="bi bi-clock-history text-success" style="font-size:3rem"></i></div>
                    <h5 class="card-title">Payment History</h5>
                    <p class="card-text text-muted">Select a customer from Udhar to view payment history</p>
                    <a href="{{ route('admin.reports.customer.outstanding') }}" class="btn btn-success">
                        <i class="bi bi-arrow-right me-1"></i>Go to Udhar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
