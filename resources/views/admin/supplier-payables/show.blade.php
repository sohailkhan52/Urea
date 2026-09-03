@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-title">{{ $supplier->name }}</h1>
                <p class="text-muted">Supplier Payables Detail</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.supplier-payables.history', $supplier->id) }}" class="btn btn-info">
                    <i class="bi bi-clock-history me-1"></i> View History
                </a>
                <a href="{{ route('admin.supplier-payables.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- Supplier Info -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Company:</strong> {{ $supplier->company_name ?? '-' }}</p>
                    <p><strong>Phone:</strong> {{ $supplier->phone ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Email:</strong> {{ $supplier->email ?? '-' }}</p>
                    <p><strong>Address:</strong> {{ $supplier->address ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Purchases</p>
                    <h4 class="mb-0">Rs. {{ number_format($total_purchases, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card success">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Paid</p>
                    <h4 class="mb-0">Rs. {{ number_format($total_paid, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card warning">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Returns</p>
                    <h4 class="mb-0">Rs. {{ number_format($total_returns, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card danger">
                <div class="card-body">
                    <p class="text-muted mb-1">Outstanding Payable</p>
                    <h4 class="mb-0">Rs. {{ number_format($outstanding, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Make Payment Button -->
    @if($outstanding > 0)
    <div class="mb-4">
        <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#paymentModal"
            onclick="setSupplierPayment({{ $supplier->id }}, '{{ $supplier->name }}', {{ $outstanding }})">
            <i class="bi bi-cash-coin me-2"></i> Record Cash Payment
        </button>
    </div>
    @endif

    <!-- Purchases List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Purchases</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>PO Number</th>
                            <th>Date</th>
                            <th>Warehouse</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Outstanding</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchases as $purchase)
                        <tr>
                            <td>
                                <a href="{{ route('admin.purchases.show', $purchase->id) }}" target="_blank">
                                    {{ $purchase->purchase_number }}
                                </a>
                            </td>
                            <td>{{ $purchase->purchase_date->format('d M Y') }}</td>
                            <td>{{ $purchase->warehouse->name ?? '-' }}</td>
                            <td class="text-end">Rs. {{ number_format($purchase->total_amount, 2) }}</td>
                            <td class="text-end">Rs. {{ number_format($purchase->paid_amount, 2) }}</td>
                            <td class="text-end">
                                <strong>Rs. {{ number_format(max(0, $purchase->total_amount - $purchase->paid_amount), 2) }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-{{ $purchase->payment_status_badge }}">
                                    {{ $purchase->payment_status_label }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Cash Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <input type="text" id="supplierName" class="form-control" readonly>
                        <input type="hidden" id="supplierId" name="supplier_id">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Outstanding Amount</label>
                        <input type="text" id="outstandingAmount" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Amount</label>
                        <input type="number" id="paymentAmount" name="amount" class="form-control" step="0.01" min="0" required>
                        <small class="text-muted">Max: <span id="maxAmount"></span></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference (Optional)</label>
                        <input type="text" name="reference" class="form-control" placeholder="Transaction ID, etc.">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Add any notes..."></textarea>
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

<script>
function setSupplierPayment(supplierId, supplierName, outstanding) {
    document.getElementById('supplierId').value = supplierId;
    document.getElementById('supplierName').value = supplierName;
    document.getElementById('outstandingAmount').value = 'Rs. ' + outstanding.toLocaleString('en-PK', {minimumFractionDigits: 2});
    document.getElementById('maxAmount').textContent = 'Rs. ' + outstanding.toLocaleString('en-PK', {minimumFractionDigits: 2});
    document.getElementById('paymentAmount').max = outstanding;
    document.getElementById('paymentAmount').value = '';
}

document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const supplierId = document.getElementById('supplierId').value;
    const form = this;
    
    fetch(`/admin/supplier-payables/${supplierId}/payment`, {
        method: 'POST',
        body: new FormData(form),
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Payment recorded successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error recording payment');
    });
});
</script>
@endsection
