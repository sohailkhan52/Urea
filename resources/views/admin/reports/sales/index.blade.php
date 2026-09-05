@extends('layouts.admin')

@section('title', 'Sale Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Sale Report</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Sale Report</h1>
        <div>
            <button type="button" class="btn btn-info me-2" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Report
            </button>
            <button type="button" class="btn btn-danger" id="deleteSelectedBtn" disabled onclick="confirmBulkDelete()">
                <i class="bi bi-trash me-1"></i> Delete Selected
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    @if($totals && $totals->total_sales > 0)
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Sales</p>
                            <h4 class="mb-0">{{ number_format($totals->total_sales) }}</h4>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-receipt fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
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
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Paid</p>
                            <h4 class="mb-0">Rs. {{ number_format($totals->total_paid_sum, 2) }}</h4>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-check-circle fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Outstanding</p>
                            <h4 class="mb-0">Rs. {{ number_format($totals->total_udhar_sum, 2) }}</h4>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-exclamation-triangle fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Profit/Loss Summary Cards --}}
    @if($totals->sales_with_cost_data > 0)
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-success bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Net Revenue</p>
                            <h5 class="mb-0 text-success">Rs. {{ number_format($totals->total_revenue, 2) }}</h5>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-graph-up fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total COGS</p>
                            <h5 class="mb-0 text-danger">Rs. {{ number_format($totals->total_cogs, 2) }}</h5>
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-cart-dash fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-{{ $totals->net_profit >= 0 ? 'success' : 'danger' }} bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Net Profit</p>
                            <h5 class="mb-0 text-{{ $totals->net_profit >= 0 ? 'success' : 'danger' }}">
                                Rs. {{ number_format($totals->net_profit, 2) }}
                            </h5>
                            <small class="text-muted">
                                Profit: Rs. {{ number_format($totals->total_profit, 2) }} | 
                                Loss: Rs. {{ number_format($totals->total_loss, 2) }}
                            </small>
                        </div>
                        <div class="text-{{ $totals->net_profit >= 0 ? 'success' : 'danger' }}">
                            <i class="bi bi-{{ $totals->net_profit >= 0 ? 'arrow-up-circle' : 'arrow-down-circle' }} fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Avg. Margin</p>
                            <h5 class="mb-0 text-info">{{ number_format($totals->avg_margin, 2) }}%</h5>
                            <small class="text-muted">{{ $totals->sales_with_cost_data }} sales with cost data</small>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-percent fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">
                <i class="bi bi-funnel me-2"></i>Filters
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.reports.sales.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    {{-- Search --}}
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Invoice number or customer name">
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

                    {{-- Customer --}}
                    <div class="col-md-2">
                        <label for="customer_id" class="form-label">Customer</label>
                        <select class="form-select" id="customer_id" name="customer_id">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Family --}}
                    <div class="col-md-2">
                        <label for="family_id" class="form-label">Family</label>
                        <select class="form-select" id="family_id" name="family_id">
                            <option value="">All Families</option>
                            @foreach($families as $family)
                            <option value="{{ $family->id }}" {{ request('family_id') == $family->id ? 'selected' : '' }}>
                                {{ $family->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Warehouse --}}
                    <div class="col-md-2">
                        <label for="warehouse_id" class="form-label">Warehouse</label>
                        <select class="form-select" id="warehouse_id" name="warehouse_id">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
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
                        <a href="{{ route('admin.reports.sales.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Sales Report Table --}}
    <div class="card">
        <div class="card-body">
            @if($sales->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th>Sale No.</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Family</th>
                            <th>Warehouse</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Outstanding</th>
                            <th class="text-end">COGS</th>
                            <th class="text-end">Profit/Loss</th>
                            <th class="text-end">Margin %</th>
                            <th>Payment Status</th>
                            <th>Created By</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                        <tr>
                            <td>
                                <input type="checkbox" 
                                       class="form-check-input sale-checkbox" 
                                       value="{{ $sale->id }}"
                                       data-has-returns="{{ $sale->returns_count > 0 ? 'true' : 'false' }}"
                                       onchange="updateDeleteButton()">
                            </td>
                            <td>
                                <a href="{{ route('admin.reports.sales.show', $sale) }}" class="text-decoration-none fw-bold">
                                    {{ $sale->invoice_number }}
                                </a>
                            </td>
                            <td>{{ $sale->sale_date->format('d M Y') }}</td>
                            <td>
                                @if($sale->customer)
                                    {{ $sale->customer->name }}
                                    @if($sale->customer->phone)
                                        <br><small class="text-muted">{{ $sale->customer->phone }}</small>
                                    @endif
                                @else
                                    {{ $sale->walkin_customer_name ?? 'Walk-in' }}
                                @endif
                            </td>
                            <td>
                                @if($sale->family)
                                    <span class="badge bg-info">{{ $sale->family->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $sale->warehouse->name }}</td>
                            <td class="text-end">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                            <td class="text-end">Rs. {{ number_format($sale->paid_amount, 2) }}</td>
                            <td class="text-end">Rs. {{ number_format($sale->due_amount, 2) }}</td>
                            <td class="text-end">
                                @if($sale->has_cost_data)
                                    Rs. {{ number_format($sale->total_cogs, 2) }}
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($sale->has_cost_data)
                                    @if($sale->profit_status === 'profit')
                                        <span class="text-success fw-bold">
                                            <i class="bi bi-arrow-up-circle me-1"></i>Rs. {{ number_format($sale->gross_profit, 2) }}
                                        </span>
                                    @elseif($sale->profit_status === 'loss')
                                        <span class="text-danger fw-bold">
                                            <i class="bi bi-arrow-down-circle me-1"></i>Rs. {{ number_format(abs($sale->gross_profit), 2) }}
                                        </span>
                                    @else
                                        <span class="text-secondary fw-bold">
                                            <i class="bi bi-dash-circle me-1"></i>Rs. 0.00
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($sale->has_cost_data && $sale->net_revenue > 0)
                                    <span class="badge bg-{{ $sale->profit_margin_percentage >= 20 ? 'success' : ($sale->profit_margin_percentage >= 10 ? 'warning' : 'danger') }}">
                                        {{ number_format($sale->profit_margin_percentage, 1) }}%
                                    </span>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($sale->paid_amount >= $sale->total_amount)
                                    <span class="badge bg-success">Paid</span>
                                @elseif($sale->paid_amount > 0)
                                    <span class="badge bg-warning">Partial</span>
                                @else
                                    <span class="badge bg-danger">Unpaid</span>
                                @endif
                            </td>
                            <td>
                                @if($sale->creator)
                                    <small>{{ $sale->creator->name }}</small>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.reports.sales.show', $sale) }}" 
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
                    Showing {{ $sales->firstItem() ?? 0 }} to {{ $sales->lastItem() ?? 0 }} of {{ $sales->total() }} sales
                </div>
                <div>
                    {{ $sales->links() }}
                </div>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="text-muted mt-2">No sales found for the selected filters.</p>
                <a href="{{ route('admin.reports.sales.index') }}" class="btn btn-outline-secondary">
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
                <p class="mb-3">You have selected <strong id="selectedCount">0</strong> sale(s) for deletion.</p>
                <div class="alert alert-warning" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> Deleting these sales may reverse related stock, payment, and Udhar records.
                </div>
                <p class="mb-0">Are you sure you want to continue?</p>
                <div id="returnWarning" class="alert alert-danger mt-3" style="display: none;">
                    <i class="bi bi-info-circle me-2"></i>
                    Some selected sales have return records and cannot be deleted.
                </div>
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
<form id="bulkDeleteForm" action="{{ route('admin.reports.sales.bulk-delete') }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
    // Select all checkbox functionality
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.sale-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        updateDeleteButton();
    }

    // Update delete button state
    function updateDeleteButton() {
        const checkboxes = document.querySelectorAll('.sale-checkbox:checked');
        const deleteBtn = document.getElementById('deleteSelectedBtn');
        deleteBtn.disabled = checkboxes.length === 0;
    }

    // Show bulk delete confirmation
    function confirmBulkDelete() {
        console.log('confirmBulkDelete() called');
        
        const checkboxes = document.querySelectorAll('.sale-checkbox:checked');
        const selectedCount = checkboxes.length;
        console.log('Selected count:', selectedCount);
        
        if (selectedCount === 0) {
            alert('Please select at least one sale to delete.');
            return;
        }

        // Check if any selected sales have returns
        let hasReturns = false;
        checkboxes.forEach(checkbox => {
            if (checkbox.dataset.hasReturns === 'true') {
                hasReturns = true;
            }
        });

        // Update modal content
        const selectedCountElement = document.getElementById('selectedCount');
        if (selectedCountElement) {
            selectedCountElement.textContent = selectedCount;
            console.log('Updated selectedCount element');
        } else {
            console.error('selectedCount element not found!');
        }
        
        const returnWarningElement = document.getElementById('returnWarning');
        if (returnWarningElement) {
            if (hasReturns) {
                returnWarningElement.style.display = 'block';
                console.log('Showed return warning');
            } else {
                returnWarningElement.style.display = 'none';
                console.log('Hid return warning');
            }
        } else {
            console.error('returnWarning element not found!');
        }

        // Show modal
        const modalElement = document.getElementById('bulkDeleteModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            console.log('Modal shown');
        } else {
            console.error('bulkDeleteModal element not found!');
            alert('Error: Modal not found. Please refresh the page and try again.');
        }
    }

    // Execute bulk delete
    function executeBulkDelete() {
        console.log('executeBulkDelete() called');
        
        const checkboxes = document.querySelectorAll('.sale-checkbox:checked');
        const saleIds = Array.from(checkboxes).map(cb => cb.value);
        console.log('Selected sale IDs:', saleIds);
        
        // Close the modal first
        const modalElement = document.getElementById('bulkDeleteModal');
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        }
        
        // Clear previous inputs
        const form = document.getElementById('bulkDeleteForm');
        console.log('Form found:', form);
        
        if (!form) {
            console.error('bulkDeleteForm not found!');
            alert('Error: Form not found. Please refresh the page and try again.');
            return;
        }
        
        const oldInputs = form.querySelectorAll('input[name^="sale_ids"]');
        console.log('Found old inputs to remove:', oldInputs.length);
        oldInputs.forEach(input => input.remove());
        
        // Add each ID as a separate hidden input
        saleIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'sale_ids[]';
            input.value = id;
            form.appendChild(input);
            console.log('Added input for sale ID:', id);
        });
        
        console.log('Form inputs before submission:');
        console.log(new FormData(form));
        
        console.log('Submitting form to:', form.action);
        
        // Submit form with a small delay to allow modal to close
        setTimeout(() => {
            try {
                form.submit();
                console.log('Form submitted successfully');
            } catch (e) {
                console.error('Error submitting form:', e);
                alert('Error submitting form: ' + e.message);
            }
        }, 100);
    }

    // Update delete button on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateDeleteButton();
    });
</script>
@endpush
