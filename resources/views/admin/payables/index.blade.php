@extends('layouts.admin')

@section('title', 'Supplier Payables Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Supplier Payables Management</h1>
        <div class="btn-group">
            <a href="{{ route('admin.payables.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
            </a>
            <a href="{{ route('admin.payables.aging') }}" class="btn btn-outline-info">
                <i class="bi bi-graph-up me-1"></i> Aging Report
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Suppliers with Payables</h6>
                            <h3 class="mb-0">{{ $stats['suppliers_with_payables'] }}</h3>
                        </div>
                        <div class="ms-auto">
                            <i class="bi bi-building text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Outstanding Payables</h6>
                            <h3 class="mb-0">Rs. {{ number_format($stats['total_outstanding_payable'], 2) }}</h3>
                        </div>
                        <div class="ms-auto">
                            <i class="bi bi-cash-coin text-danger" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Partial Purchases</h6>
                            <h3 class="mb-0">{{ $stats['partial_purchases'] }}</h3>
                        </div>
                        <div class="ms-auto">
                            <i class="bi bi-receipt text-warning" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Unpaid Purchases</h6>
                            <h3 class="mb-0">{{ $stats['unpaid_purchases'] }}</h3>
                        </div>
                        <div class="ms-auto">
                            <i class="bi bi-exclamation-circle-fill text-danger" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search and Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.payables.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ $filters['search'] }}"
                               placeholder="Search by supplier name, company or phone">
                    </div>
                    <div class="col-md-2">
                        <label for="payable_min" class="form-label">Min Payable (Rs.)</label>
                        <input type="number" 
                               class="form-control" 
                               id="payable_min" 
                               name="payable_min" 
                               value="{{ $filters['payable_min'] }}"
                               placeholder="Minimum">
                    </div>
                    <div class="col-md-2">
                        <label for="payable_max" class="form-label">Max Payable (Rs.)</label>
                        <input type="number" 
                               class="form-control" 
                               id="payable_max" 
                               name="payable_max" 
                               value="{{ $filters['payable_max'] }}"
                               placeholder="Maximum">
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.payables.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Suppliers with Payables Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Supplier Name</th>
                        <th>Company</th>
                        <th>Phone</th>
                        <th class="text-end">Total Purchases</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Total Paid</th>
                        <th class="text-end">Total Payable</th>
                        <th>Last Payment</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                    <tr>
                        <td>
                            <strong>{{ $supplier['supplier_name'] }}</strong>
                        </td>
                        <td>{{ $supplier['company_name'] ?? 'N/A' }}</td>
                        <td>{{ $supplier['phone'] ?? 'N/A' }}</td>
                        <td class="text-end">{{ $supplier['total_purchases'] }}</td>
                        <td class="text-end">Rs. {{ number_format($supplier['total_purchase_amount'], 2) }}</td>
                        <td class="text-end">Rs. {{ number_format($supplier['total_paid_amount'], 2) }}</td>
                        <td class="text-end">
                            <strong class="text-danger">
                                Rs. {{ number_format($supplier['total_payable'], 2) }}
                            </strong>
                        </td>
                        <td>{{ $supplier['last_payment_date'] }}</td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.payables.details', $supplier['supplier_id']) }}" 
                                   class="btn btn-outline-primary" 
                                   title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.payables.ledger', $supplier['supplier_id']) }}" 
                                   class="btn btn-outline-info" 
                                   title="View Ledger">
                                    <i class="bi bi-book"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 2rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">No suppliers with outstanding payables found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="card-footer bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ $suppliers->firstItem() ?? 0 }} to {{ $suppliers->lastItem() ?? 0 }} 
                    of {{ $suppliers->total() }} suppliers
                </small>
                {{ $suppliers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
