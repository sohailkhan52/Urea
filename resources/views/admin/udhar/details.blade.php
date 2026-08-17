@extends('layouts.admin')

@section('title', 'Udhar Details - ' . $customer->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Udhar Details</h1>
            <p class="text-muted mb-0">{{ $customer->name }}</p>
        </div>
        <a href="{{ route('admin.udhar.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    {{-- Customer Summary --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Customer Name</h6>
                    <p class="mb-0"><strong>{{ $customerTotals['name'] }}</strong></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Customer Type</h6>
                    <p class="mb-0">
                        <span class="badge bg-primary">{{ $customerTotals['type_label'] }}</span>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Phone</h6>
                    <p class="mb-0"><strong>{{ $customerTotals['phone'] ?? 'N/A' }}</strong></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Sales</h6>
                    <p class="mb-0"><strong>{{ $customerTotals['total_sales'] }}</strong></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Totals Summary --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Amount</h6>
                    <h4 class="mb-0 text-primary">Rs. {{ number_format($customerTotals['total_amount'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Paid</h6>
                    <h4 class="mb-0 text-success">Rs. {{ number_format($customerTotals['total_paid'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Udhar</h6>
                    <h4 class="mb-0 text-danger">Rs. {{ number_format($customerTotals['total_udhar'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Days Overdue</h6>
                    <h4 class="mb-0 text-info">{{ $summary['days_overdue'] }} days</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Outstanding Invoices --}}
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Outstanding Invoices</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Warehouse</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Paid Amount</th>
                        <th class="text-end">Due Amount</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($outstandingSales as $sale)
                    <tr>
                        <td><strong>{{ $sale['invoice_number'] }}</strong></td>
                        <td>{{ $sale['sale_date'] }}</td>
                        <td>{{ $sale['warehouse'] }}</td>
                        <td class="text-end">Rs. {{ number_format($sale['total_amount'], 2) }}</td>
                        <td class="text-end">
                            <span class="text-success">Rs. {{ number_format($sale['paid_amount'], 2) }}</span>
                        </td>
                        <td class="text-end">
                            <strong class="text-danger">Rs. {{ number_format($sale['due_amount'], 2) }}</strong>
                        </td>
                        <td>
                            @if($sale['payment_status'] == 'unpaid')
                                <span class="badge bg-danger">Unpaid</span>
                            @elseif($sale['payment_status'] == 'partial')
                                <span class="badge bg-warning">Partial</span>
                            @else
                                <span class="badge bg-success">Paid</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.sales.show', $sale['id']) }}" 
                                   class="btn btn-outline-info" 
                                   title="View Invoice"
                                   target="_blank">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($sale['due_amount'] > 0)
                                <button class="btn btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#paymentModal"
                                        data-sale-id="{{ $sale['id'] }}"
                                        data-invoice="{{ $sale['invoice_number'] }}"
                                        data-due-amount="{{ $sale['due_amount'] }}"
                                        title="Record Payment">
                                    <i class="bi bi-cash-coin"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="bi bi-check-circle" style="font-size: 2rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">No outstanding invoices</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Payment Recording Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="paymentForm" method="POST" action="{{ route('admin.udhar.recordPayment', $customer) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="invoiceDisplay" class="form-label">Invoice Number</label>
                        <input type="text" class="form-control" id="invoiceDisplay" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="outstandingDisplay" class="form-label">Outstanding Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">Rs.</span>
                            <input type="text" class="form-control" id="outstandingDisplay" disabled>
                        </div>
                    </div>

                    <input type="hidden" id="saleId" name="sale_id">

                    <div class="mb-3">
                        <label for="amount" class="form-label">Payment Amount *</label>
                        <div class="input-group">
                            <span class="input-group-text">Rs.</span>
                            <input type="number" 
                                   class="form-control" 
                                   id="amount" 
                                   name="amount" 
                                   step="0.01"
                                   min="0"
                                   placeholder="0.00"
                                   required>
                        </div>
                        <small class="text-muted">Maximum: <span id="maxAmount">0</span></small>
                    </div>

                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Payment Date *</label>
                        <input type="date" 
                               class="form-control" 
                               id="payment_date" 
                               name="payment_date" 
                               value="{{ date('Y-m-d') }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method *</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="">-- Select Payment Method --</option>
                            @foreach(\App\Models\Payment::$methods as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="reference_number" class="form-label">Reference Number</label>
                        <input type="text" 
                               class="form-control" 
                               id="reference_number" 
                               name="reference_number" 
                               placeholder="e.g., Cheque #, Transaction ID">
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" 
                                  id="notes" 
                                  name="notes" 
                                  rows="3"
                                  placeholder="Additional notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentModal = document.getElementById('paymentModal');
    
    paymentModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const saleId = button.dataset.saleId;
        const invoice = button.dataset.invoice;
        const dueAmount = parseFloat(button.dataset.dueAmount);

        document.getElementById('saleId').value = saleId;
        document.getElementById('invoiceDisplay').value = invoice;
        document.getElementById('outstandingDisplay').value = dueAmount.toFixed(2);
        document.getElementById('maxAmount').textContent = dueAmount.toFixed(2);
        document.getElementById('amount').max = dueAmount;
        document.getElementById('amount').value = '';
    });

    // Validate amount doesn't exceed outstanding
    const amountInput = document.getElementById('amount');
    amountInput.addEventListener('change', function() {
        const max = parseFloat(this.max);
        if (parseFloat(this.value) > max) {
            this.value = max;
        }
    });
});
</script>
@endsection
