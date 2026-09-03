@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <h1 class="page-title">Supplier Payables</h1>
        <p class="text-muted">Manage outstanding payments to suppliers</p>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Outstanding</p>
                            <h3 class="mb-0">Rs. {{ number_format($summary['total_outstanding'], 2) }}</h3>
                        </div>
                        <div class="text-primary" style="font-size: 2rem;">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Suppliers With Payables</p>
                            <h3 class="mb-0">{{ $summary['supplier_count'] }}</h3>
                        </div>
                        <div class="text-warning" style="font-size: 2rem;">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Paid</p>
                            <h3 class="mb-0">Rs. {{ number_format($summary['total_paid'], 2) }}</h3>
                        </div>
                        <div class="text-success" style="font-size: 2rem;">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Purchases</p>
                            <h3 class="mb-0">Rs. {{ number_format($summary['total_purchases'], 2) }}</h3>
                        </div>
                        <div class="text-info" style="font-size: 2rem;">
                            <i class="bi bi-cart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Supplier Payables Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Supplier Payables</h5>
        </div>
        <div class="card-body">
            @if($suppliers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Supplier</th>
                                <th>Company</th>
                                <th class="text-end">Total Purchases</th>
                                <th class="text-end">Total Paid</th>
                                <th class="text-end">Total Returns</th>
                                <th class="text-end">Outstanding Payable</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($suppliers as $supplier)
                            <tr>
                                <td>
                                    <strong>{{ $supplier->name }}</strong><br>
                                    <small class="text-muted">{{ $supplier->phone }}</small>
                                </td>
                                <td>{{ $supplier->company_name ?? '-' }}</td>
                                <td class="text-end">Rs. {{ number_format($supplier->total_purchases, 2) }}</td>
                                <td class="text-end">Rs. {{ number_format($supplier->total_paid, 2) }}</td>
                                <td class="text-end">Rs. {{ number_format($supplier->total_returns, 2) }}</td>
                                <td class="text-end">
                                    <strong class="text-danger">Rs. {{ number_format($supplier->outstanding_payable, 2) }}</strong>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.supplier-payables.show', $supplier->id) }}" class="btn btn-outline-primary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#paymentModal" 
                                            onclick="setSupplierPayment({{ $supplier->id }}, '{{ $supplier->name }}', {{ $supplier->outstanding_payable }})">
                                            <i class="bi bi-cash-coin"></i>
                                        </button>
                                        <a href="{{ route('admin.supplier-payables.history', $supplier->id) }}" class="btn btn-outline-info" title="Payment History">
                                            <i class="bi bi-clock-history"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">No Outstanding Supplier Payables</p>
                    <small>All supplier purchases are fully paid.</small>
                </div>
            @endif
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
                        <input type="text" name="reference" class="form-control" placeholder="Cheque number, transaction ID, etc.">
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
