@extends('layouts.admin')

@section('content')
<div class="container-fluid supplier-payables-detail-page">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-title">{{ $supplier->name }}</h1>
                <p class="text-muted">Supplier Payables Detail</p>
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
                    <h4 class="mb-0">Rs. {{ number_format($total_purchases, 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card success">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Paid</p>
                    <h4 class="mb-0">Rs. {{ number_format($total_paid, 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card warning">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Returns</p>
                    <h4 class="mb-0">Rs. {{ number_format($total_returns, 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card danger">
                <div class="card-body">
                    <p class="text-muted mb-1">Outstanding Payable</p>
                    <h4 class="mb-0">Rs. {{ number_format($outstanding, 0) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Make Payment Button -->
    @if($outstanding > 0)
    <div class="mb-4 supplier-payment-action">
        <button type="button" class="btn btn-success btn-lg supplier-payment-button" data-bs-toggle="modal" data-bs-target="#paymentModal"
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
                            <th class="text-end">Total</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Returns</th>
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
                            <td class="text-end">Rs. {{ number_format($purchase->total_amount, 0) }}</td>
                            <td class="text-end">Rs. {{ number_format($purchase->paid_amount, 0) }}</td>
                            <td class="text-end">
                                Rs. {{ number_format($purchase->total_returns, 0) }}
                            </td>
                            <td class="text-end">
                                <strong>Rs. {{ number_format($purchase->payable_amount, 0) }}</strong>
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

<style>
    .supplier-payables-detail-page > .row.mb-4 > .col-md-3 {
        display: flex;
    }

    .supplier-payables-detail-page > .row.mb-4 > .col-md-3 .card {
        width: 100%;
        height: 80%;
    }

    #paymentModal {
        z-index: 1080;
    }

    .modal-backdrop {
        z-index: 1070;
    }

    body.modal-open .sidebar-edge-toggle {
        z-index: 1040 !important;
    }

    @media (max-width: 1024px) {
        #paymentModal .modal-dialog {
            width: calc(100% - 32px);
            max-width: 520px;
            max-height: calc(100dvh - 24px);
            margin: 12px auto;
        }

        #paymentModal .modal-content {
            display: flex;
            max-height: calc(100dvh - 24px);
            overflow: hidden;
        }

        #paymentModal #paymentForm {
            display: flex;
            min-height: 0;
            flex: 1 1 auto;
            flex-direction: column;
            overflow: hidden;
        }

        #paymentModal .modal-header,
        #paymentModal .modal-footer {
            flex: 0 0 auto;
        }

        #paymentModal .modal-body {
            min-height: 0;
            flex: 1 1 auto;
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }
    }

    @media (min-width: 576px) and (max-width: 1024px) {
        .supplier-payables-detail-page > .row.mb-4 > .col-md-3 h4 {
            font-size: 0.95rem;
            white-space: nowrap;
        }

        .supplier-payables-detail-page .supplier-payment-action {
            margin-bottom: 14px !important;
        }

        .supplier-payables-detail-page .supplier-payment-button {
            min-height: 38px;
            padding: 7px 12px;
            font-size: 0.88rem;
            line-height: 1.2;
        }

        .supplier-payables-detail-page .supplier-payment-button .bi {
            margin-right: 6px !important;
        }
    }

    @media (max-width: 575.98px) {
        body.modal-open .sidebar-edge-toggle {
            z-index: 1040 !important;
        }

        #paymentModal .modal-dialog {
            width: calc(100% - 32px);
            max-width: 380px;
            max-height: calc(100dvh - 40px);
            margin: 20px auto;
        }

        #paymentModal .modal-content {
            max-height: calc(100dvh - 40px);
        }

        #paymentModal #paymentForm {
            display: flex;
            min-height: 0;
            flex-direction: column;
        }

        #paymentModal .modal-header,
        #paymentModal .modal-footer {
            flex: 0 0 auto;
            padding: 8px 10px;
        }

        #paymentModal .modal-title {
            font-size: 0.92rem;
        }

        #paymentModal .modal-body {
            min-height: 0;
            overflow-y: auto;
            padding: 9px 10px;
        }

        #paymentModal .modal-body .mb-3 {
            margin-bottom: 7px !important;
        }

        #paymentModal .form-label,
        #paymentModal .form-control,
        #paymentModal .modal-footer .btn {
            font-size: 0.8rem;
        }

        #paymentModal .form-control {
            min-height: 34px;
            padding: 5px 8px;
        }

        #paymentModal textarea.form-control {
            min-height: 52px;
        }

        #paymentModal .modal-footer .btn {
            min-height: 32px;
            padding: 5px 8px;
        }

        .supplier-payables-detail-page > .row.mb-4 > .col-md-3 {
            flex: 0 0 50%;
            width: 50%;
            max-width: 50%;
            margin-bottom: 8px;
        }

        .supplier-payables-detail-page > .row.mb-4 > .col-md-3 .card {
            height: 70px;
            margin-bottom: 0;
        }

        .supplier-payables-detail-page > .row.mb-4 > .col-md-3 .card-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 9px;
        }

        .supplier-payables-detail-page > .row.mb-4 > .col-md-3 p {
            font-size: 0.7rem;
            line-height: 1.15;
        }

        .supplier-payables-detail-page > .row.mb-4 > .col-md-3 h4 {
            font-size: 0.82rem;
            line-height: 1.15;
            white-space: nowrap;
        }

        .supplier-payables-detail-page .supplier-payment-action {
            margin-bottom: 12px !important;
        }

        .supplier-payables-detail-page .supplier-payment-button {
            display: inline-flex;
            width: auto;
            max-width: 100%;
            min-height: 36px;
            align-items: center;
            justify-content: center;
            padding: 6px 10px;
            font-size: 0.78rem;
            line-height: 1.2;
            white-space: nowrap;
        }

        .supplier-payables-detail-page .supplier-payment-button .bi {
            margin-right: 5px !important;
        }

        .supplier-payables-detail-page .page-header .page-title {
            font-size: 1.25rem;
            line-height: 1.2;
        }

        .supplier-payables-detail-page .page-header > .row p {
            margin-bottom: 0;
            font-size: 0.82rem;
            line-height: 1.3;
        }

        .supplier-payables-detail-page > .card:first-of-type .card-body {
            padding: 12px;
            font-size: 0.82rem;
            line-height: 1.4;
        }

        .supplier-payables-detail-page > .card:first-of-type .card-body p {
            margin-bottom: 8px;
            overflow-wrap: anywhere;
        }
    }
</style>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
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
    document.getElementById('outstandingAmount').value = 'Rs. ' + Math.round(outstanding).toLocaleString('en-PK');
    document.getElementById('maxAmount').textContent = 'Rs. ' + Math.round(outstanding).toLocaleString('en-PK');
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
