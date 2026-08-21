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
                            <button class="btn btn-sm btn-outline-success" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#paymentModal"
                                    data-purchase-id="{{ $purchase['id'] }}"
                                    data-purchase-number="{{ $purchase['purchase_number'] }}"
                                    data-payable-amount="{{ $purchase['payable_amount'] }}">
                                <i class="bi bi-credit-card me-1"></i> Record Payment
                            </button>
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
</div>

{{-- Payment Modal --}}
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
</script>
@endsection
