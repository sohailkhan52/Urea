@extends('layouts.admin')

@section('title', 'Supplier Payables - ' . $supplier->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $supplier->name }}</h1>
            <p class="text-muted mb-0">Payables Details & Outstanding Invoices</p>
        </div>
        <div class="btn-group" role="group">
            @can('payables.view')
            <a href="{{ route('admin.payables.print', $supplier) }}" 
               class="btn btn-primary" 
               target="_blank"
               title="Print Payables Statement">
                <i class="bi bi-printer me-1"></i> Print Statement
            </a>
            @endcan
            <a href="{{ route('admin.payables.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Payables
            </a>
        </div>
    </div>

    {{-- Supplier Summary Card --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Supplier Information</h6>
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width: 150px;">Name:</td>
                            <td><strong>{{ $supplierTotals['name'] }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Company:</td>
                            <td>{{ $supplierTotals['company_name'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Phone:</td>
                            <td>{{ $supplierTotals['phone'] ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Payment Summary</h6>
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width: 150px;">Total Purchases:</td>
                            <td><strong>{{ $supplierTotals['total_purchases'] }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total Amount:</td>
                            <td>Rs. <strong>{{ number_format($supplierTotals['total_purchase_amount'], 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total Paid:</td>
                            <td>Rs. <strong class="text-success">{{ number_format($supplierTotals['total_paid_amount'], 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total Payable:</td>
                            <td>Rs. <strong class="text-danger">{{ number_format($supplierTotals['total_payable'], 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Outstanding Purchases Table --}}
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Outstanding Purchases</h5>
            <div class="btn-group" role="group">
                <a href="{{ route('admin.payables.transaction-history', $supplier) }}" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-clock-history me-1"></i> View History
                </a>
                <a href="{{ route('admin.payables.ledger', $supplier) }}" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-book me-1"></i> View Ledger
                </a>
            </div>
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
                        <th class="text-end">Remaining Payable</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($outstandingPurchases as $purchase)
                    <tr>
                        <td>
                            <strong>{{ $purchase['purchase_number'] }}</strong>
                        </td>
                        <td>{{ $purchase['purchase_date'] }}</td>
                        <td>{{ $purchase['warehouse'] }}</td>
                        <td class="text-end">Rs. {{ number_format($purchase['total_amount'], 2) }}</td>
                        <td class="text-end">Rs. {{ number_format($purchase['paid_amount'], 2) }}</td>
                        <td class="text-end">
                            <strong class="text-danger">
                                Rs. {{ number_format($purchase['payable_amount'], 2) }}
                            </strong>
                        </td>
                        <td>
                            <span class="badge bg-{{ $purchase['payment_status_badge'] }}">
                                {{ $purchase['payment_status_label'] }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.purchases.show', $purchase['id']) }}" 
                               class="btn btn-sm btn-outline-info"
                               target="_blank"
                               title="View Purchase">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="bi bi-check-circle" style="font-size: 2rem; color: #28a745;"></i>
                            <p class="text-muted mt-2">All purchases from this supplier are fully paid!</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Manage Total Payables Section --}}
    @if($supplierTotals['total_payable'] > 0)
    <div class="card mt-4">
        <div class="card-header bg-danger bg-opacity-10 border-danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 text-danger">
                        <i class="bi bi-credit-card me-2"></i>Manage Total Payables
                    </h5>
                    <small class="text-muted">Make payments for all outstanding amounts to this supplier</small>
                </div>
                <div class="text-end">
                    <h4 class="mb-0 text-danger">Rs. {{ number_format($supplierTotals['total_payable'], 2) }}</h4>
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
                                <h6 class="text-muted mb-1">Total Purchases</h6>
                                <p class="mb-0 fw-bold">Rs. {{ number_format($supplierTotals['total_purchase_amount'], 2) }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Paid Amount</h6>
                                <p class="mb-0 fw-bold text-success">Rs. {{ number_format($supplierTotals['total_paid_amount'], 2) }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Total Payable</h6>
                                <p class="mb-0 fw-bold text-danger">Rs. {{ number_format($supplierTotals['total_payable'], 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-success btn-lg" 
                            data-bs-toggle="modal" 
                            data-bs-target="#bulkPaymentModal"
                            title="Record Payment for Total Payables">
                        <i class="bi bi-credit-card me-2"></i>Record Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Bulk Payment Modal for Total Payables --}}
<div class="modal fade" id="bulkPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-credit-card me-2"></i>Record Payment - Total Payables
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkPaymentForm" method="POST" action="{{ route('admin.payables.recordPayment', $supplier) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Total Payables Payment:</strong> This will record a payment against the supplier's total outstanding amount and distribute it across outstanding purchases.
                    </div>

                    <div class="mb-3">
                        <label for="supplierDisplay" class="form-label">Supplier</label>
                        <input type="text" class="form-control" id="supplierDisplay" value="{{ $supplier->name }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="totalPayableDisplay" class="form-label">Total Outstanding Payables</label>
                        <div class="input-group">
                            <span class="input-group-text">Rs.</span>
                            <input type="text" class="form-control" id="totalPayableDisplay" value="{{ number_format($supplierTotals['total_payable'], 2) }}" disabled>
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
                                   max="{{ $supplierTotals['total_payable'] }}"
                                   placeholder="0.00"
                                   required>
                        </div>
                        <small class="text-muted">Maximum: Rs. {{ number_format($supplierTotals['total_payable'], 2) }}</small>
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
                            @foreach(\App\Models\PurchasePayment::$methods as $key => $label)
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
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i> Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Individual Payment Modal (kept for future use) --}}
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Supplier Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.payables.recordPayment', $supplier) }}" id="paymentForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Purchase Invoice</label>
                        <input type="hidden" id="purchaseId" name="purchase_id">
                        <input type="text" class="form-control" id="purchaseNumber" readonly>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Current Payable</label>
                                <div class="form-control-plaintext fs-5" id="currentPayable">
                                    <strong class="text-danger">Rs. 0.00</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Payment Amount <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       id="amount" 
                                       name="amount" 
                                       step="0.01" 
                                       min="0.01"
                                       required>
                                <small class="text-muted" id="remainingAfterPayment"></small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="">Select Payment Method</option>
                            @foreach(\App\Models\PurchasePayment::$methods as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" 
                               class="form-control" 
                               id="payment_date" 
                               name="payment_date"
                               value="{{ date('Y-m-d') }}"
                               min="{{ date('Y-m-d') }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="reference_number" class="form-label">Reference Number</label>
                        <input type="text" 
                               class="form-control" 
                               id="reference_number" 
                               name="reference_number"
                               placeholder="Cheque #, Bank Ref, etc.">
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" 
                                  id="notes" 
                                  name="notes"
                                  rows="2"
                                  placeholder="Additional notes (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('button[data-bs-target="#paymentModal"]').forEach(button => {
    button.addEventListener('click', function() {
        const purchaseId = this.dataset.purchaseId;
        const purchaseNumber = this.dataset.purchaseNumber;
        const payableAmount = parseFloat(this.dataset.payableAmount);
        
        document.getElementById('purchaseId').value = purchaseId;
        document.getElementById('purchaseNumber').value = purchaseNumber;
        document.getElementById('currentPayable').textContent = 'Rs. ' + payableAmount.toLocaleString('en-PK', {minimumFractionDigits: 2});
        document.getElementById('amount').max = payableAmount;
        document.getElementById('amount').value = '';
        
        // Calculate remaining payable as user types
        document.getElementById('amount').addEventListener('input', function() {
            const paymentAmount = parseFloat(this.value) || 0;
            const remaining = payableAmount - paymentAmount;
            document.getElementById('remainingAfterPayment').textContent = 
                `After payment, remaining will be: Rs. ${remaining.toLocaleString('en-PK', {minimumFractionDigits: 2})}`;
        });
    });
});

// Validate bulk payment amount
const bulkAmountInput = document.getElementById('bulk_amount');
if (bulkAmountInput) {
    bulkAmountInput.addEventListener('change', function() {
        const max = parseFloat(this.max);
        if (parseFloat(this.value) > max) {
            this.value = max;
        }
    });
}
</script>
@endsection
