@extends('layouts.admin')

@section('title', 'Udhar Details - ' . $customer->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Udhar Details</h1>
            <p class="text-muted mb-0">{{ $customer->name }}</p>
        </div>
        <div class="btn-group" role="group">
            @can('udhar.view')
            <a href="{{ route('admin.udhar.print', $customer) }}" 
               class="btn btn-primary" 
               target="_blank"
               title="Print Udhar Statement">
                <i class="bi bi-printer me-1"></i> Print Statement
            </a>
            @endcan
            <a href="{{ route('admin.udhar.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            <a href="{{ route('admin.udhar.transaction-history', $customer) }}" class="btn btn-outline-info">
                <i class="bi bi-clock-history me-1"></i> Transaction History
            </a>
        </div>
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

    {{-- Manage Udhar Section --}}
    @if($customerTotals['total_udhar'] > 0)
    <div class="card mt-4">
        <div class="card-header bg-warning bg-opacity-10 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 text-warning">
                        <i class="bi bi-credit-card me-2"></i>Manage Total Udhar
                    </h5>
                    <small class="text-muted">Manage payments for all outstanding amounts</small>
                </div>
                <div class="text-end">
                    <h4 class="mb-0 text-danger">Rs. {{ number_format($customerTotals['total_udhar'], 2) }}</h4>
                    <small class="text-muted">Total Outstanding</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Total Amount</h6>
                                <p class="mb-0 fw-bold">Rs. {{ number_format($customerTotals['total_amount'], 2) }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Paid Amount</h6>
                                <p class="mb-0 fw-bold text-success">Rs. {{ number_format($customerTotals['total_paid'], 2) }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Outstanding</h6>
                                <p class="mb-0 fw-bold text-danger">Rs. {{ number_format($customerTotals['total_udhar'], 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-primary btn-lg" 
                            data-bs-toggle="modal" 
                            data-bs-target="#bulkPaymentModal"
                            title="Record Payment for Total Udhar">
                        <i class="bi bi-cash-coin me-2"></i>Record Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Bulk Payment Modal for Total Udhar --}}
<div class="modal fade" id="bulkPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-credit-card me-2"></i>Record Payment - Total Udhar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkPaymentForm" method="POST" action="{{ route('admin.udhar.recordPayment', $customer) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Total Udhar Payment:</strong> This will record a payment against the customer's total outstanding amount and distribute it across outstanding invoices.
                    </div>

                    <div class="mb-3">
                        <label for="customerDisplay" class="form-label">Customer</label>
                        <input type="text" class="form-control" id="customerDisplay" value="{{ $customer->name }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="totalOutstandingDisplay" class="form-label">Total Outstanding Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">Rs.</span>
                            <input type="text" class="form-control" id="totalOutstandingDisplay" value="{{ number_format($customerTotals['total_udhar'], 2) }}" disabled>
                        </div>
                    </div>

                    <input type="hidden" name="bulk_payment" value="1">

                    <div class="mb-3">
                        <label for="bulk_amount" class="form-label">Payment Amount *</label>
                        <div class="input-group">
                            <span class="input-group-text">Rs.</span>
                            <input type="number" 
                                   class="form-control" 
                                   id="bulk_amount" 
                                   name="amount" 
                                   step="0.01"
                                   min="0"
                                   max="{{ $customerTotals['total_udhar'] }}"
                                   placeholder="0.00"
                                   required>
                        </div>
                        <small class="text-muted">Maximum: Rs. {{ number_format($customerTotals['total_udhar'], 2) }}</small>
                    </div>

                    <div class="mb-3">
                        <label for="bulk_payment_date" class="form-label">Payment Date *</label>
                        <input type="date" 
                               class="form-control" 
                               id="bulk_payment_date" 
                               name="payment_date" 
                               value="{{ date('Y-m-d') }}"
                               min="{{ date('Y-m-d') }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="bulk_payment_method" class="form-label">Payment Method *</label>
                        <select class="form-select" id="bulk_payment_method" name="payment_method" required>
                            <option value="">-- Select Payment Method --</option>
                            @foreach(\App\Models\Payment::$methods as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="bulk_reference_number" class="form-label">Reference Number</label>
                        <input type="text" 
                               class="form-control" 
                               id="bulk_reference_number" 
                               name="reference_number" 
                               placeholder="e.g., Cheque #, Transaction ID">
                    </div>

                    <div class="mb-3">
                        <label for="bulk_notes" class="form-label">Notes</label>
                        <textarea class="form-control" 
                                  id="bulk_notes" 
                                  name="notes" 
                                  rows="3"
                                  placeholder="Additional notes for this payment"></textarea>
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

{{-- Individual Payment Modal (kept for future use) --}}
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
                               min="{{ date('Y-m-d') }}"
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

    // Validate amount doesn't exceed outstanding for individual payments
    const amountInput = document.getElementById('amount');
    amountInput.addEventListener('change', function() {
        const max = parseFloat(this.max);
        if (parseFloat(this.value) > max) {
            this.value = max;
        }
    });

    // Validate bulk payment amount
    const bulkAmountInput = document.getElementById('bulk_amount');
    bulkAmountInput.addEventListener('change', function() {
        const max = parseFloat(this.max);
        if (parseFloat(this.value) > max) {
            this.value = max;
        }
    });
});
</script>
@endsection
