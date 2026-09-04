@extends('layouts.admin')

@section('title', 'Purchase Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Purchase Report</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Purchase Report</h1>
        <div>
            <button type="button" class="btn btn-danger" id="deleteSelectedBtn" disabled onclick="confirmBulkDelete()">
                <i class="bi bi-trash me-1"></i> Delete Selected
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    @if($totals && $totals->total_purchases > 0)
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Purchases</p>
                            <h4 class="mb-0">{{ number_format($totals->total_purchases) }}</h4>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-cart fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Amount</p>
                            <h4 class="mb-0">Rs. {{ number_format($totals->total_amount_sum, 2) }}</h4>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-currency-dollar fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Payable</p>
                            <h4 class="mb-0">Rs. {{ number_format($totals->total_payable_sum, 2) }}</h4>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-wallet2 fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">
                <i class="bi bi-funnel me-2"></i>Filters
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.reports.purchases.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    {{-- Search --}}
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Purchase number or supplier name">
                    </div>

                    {{-- Date From --}}
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_from" 
                               name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>

                    {{-- Date To --}}
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_to" 
                               name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>

                    {{-- Supplier --}}
                    <div class="col-md-3">
                        <label for="supplier_id" class="form-label">Supplier</label>
                        <select class="form-select" id="supplier_id" name="supplier_id">
                            <option value="">All Suppliers</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Payment Status --}}
                    <div class="col-md-2">
                        <label for="payment_status" class="form-label">Payment Status</label>
                        <select class="form-select" id="payment_status" name="payment_status">
                            <option value="">All Statuses</option>
                            <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partially Paid</option>
                            <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        </select>
                    </div>

                    {{-- Created By --}}
                    @if($creators->count() > 0)
                    <div class="col-md-2">
                        <label for="created_by" class="form-label">Created By</label>
                        <select class="form-select" id="created_by" name="created_by">
                            <option value="">All Users</option>
                            @foreach($creators as $creator)
                            <option value="{{ $creator->id }}" {{ request('created_by') == $creator->id ? 'selected' : '' }}>
                                {{ $creator->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Apply Filters
                        </button>
                        <a href="{{ route('admin.reports.purchases.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Purchases Report Table --}}
    <div class="card">
        <div class="card-body">
            @if($purchases->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th>Purchase No.</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Warehouse</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Payable</th>
                            <th>Payment Status</th>
                            <th>Created By</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchases as $purchase)
                        <tr>
                            <td>
                                <input type="checkbox" 
                                       class="form-check-input purchase-checkbox" 
                                       value="{{ $purchase->id }}"
                                       onchange="updateDeleteButton()">
                            </td>
                            <td>
                                <a href="{{ route('admin.reports.purchases.show', $purchase) }}" class="text-decoration-none fw-bold">
                                    {{ $purchase->purchase_number }}
                                </a>
                            </td>
                            <td>{{ $purchase->purchase_date->format('d M Y') }}</td>
                            <td>
                                {{ $purchase->supplier->name }}
                                @if($purchase->supplier->phone)
                                    <br><small class="text-muted">{{ $purchase->supplier->phone }}</small>
                                @endif
                            </td>
                            <td>{{ $purchase->warehouse->name }}</td>
                            <td class="text-end">Rs. {{ number_format($purchase->total_amount, 2) }}</td>
                            <td class="text-end">Rs. {{ number_format($purchase->paid_amount, 2) }}</td>
                            <td class="text-end">Rs. {{ number_format($purchase->total_amount - $purchase->paid_amount, 2) }}</td>
                            <td>
                                @if($purchase->payment_status === 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($purchase->payment_status === 'partial')
                                    <span class="badge bg-warning">Partial</span>
                                @else
                                    <span class="badge bg-danger">Unpaid</span>
                                @endif
                            </td>
                            <td>
                                @if($purchase->creator)
                                    <small>{{ $purchase->creator->name }}</small>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.reports.purchases.show', $purchase) }}" 
                                   class="btn btn-sm btn-outline-primary"
                                   title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $purchases->firstItem() ?? 0 }} to {{ $purchases->lastItem() ?? 0 }} of {{ $purchases->total() }} purchases
                </div>
                <div>
                    {{ $purchases->links() }}
                </div>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="text-muted mt-2">No purchases found for the selected filters.</p>
                <a href="{{ route('admin.reports.purchases.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i> Clear Filters
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Bulk Delete Confirmation Modal --}}
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="bulkDeleteModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>Confirm Bulk Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">You have selected <strong id="selectedCount">0</strong> purchase(s) for deletion.</p>
                <div class="alert alert-warning" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> Deleting these purchases may reverse related stock and payment records.
                </div>
                <p class="mb-0">Are you sure you want to continue?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="executeBulkDelete()">
                    <i class="bi bi-trash me-1"></i> Delete Selected
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Hidden form for bulk delete --}}
<form id="bulkDeleteForm" action="{{ route('admin.reports.purchases.bulk-delete') }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
    // Select all checkbox functionality
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.purchase-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        updateDeleteButton();
    }

    // Update delete button state
    function updateDeleteButton() {
        const checkboxes = document.querySelectorAll('.purchase-checkbox:checked');
        const deleteBtn = document.getElementById('deleteSelectedBtn');
        deleteBtn.disabled = checkboxes.length === 0;
    }

    // Show bulk delete confirmation
    function confirmBulkDelete() {
        const checkboxes = document.querySelectorAll('.purchase-checkbox:checked');
        const selectedCount = checkboxes.length;
        
        if (selectedCount === 0) {
            alert('Please select at least one purchase to delete.');
            return;
        }

        // Update modal content
        document.getElementById('selectedCount').textContent = selectedCount;
        document.getElementById('returnWarning').style.display = 'none';

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));
        modal.show();
    }

    // Execute bulk delete
    function executeBulkDelete() {
        const checkboxes = document.querySelectorAll('.purchase-checkbox:checked');
        const purchaseIds = Array.from(checkboxes).map(cb => cb.value);
        
        // Clear previous inputs
        const form = document.getElementById('bulkDeleteForm');
        const oldInputs = form.querySelectorAll('input[name^="purchase_ids"]');
        oldInputs.forEach(input => input.remove());
        
        // Add each ID as a separate hidden input
        purchaseIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'purchase_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        
        // Submit form
        form.submit();
    }

    // Update delete button on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateDeleteButton();
    });
</script>
@endpush
