@extends('layouts.admin')

@section('title', 'Customer Account - ' . $customer->name)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">{{ $customer->name }}</h1>
                <p class="text-muted mb-0">Individual Customer Account</p>
            </div>
            <a href="{{ route('admin.udhar.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    {{-- INDIVIDUAL ACCOUNT --}}
    <div class="card mb-4 border-info">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="bi bi-person-circle me-2"></i>INDIVIDUAL ACCOUNT
                <span class="badge bg-light text-info ms-2">Individual</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-3">
                    <p class="text-muted mb-1 small">Total Sales</p>
                    <h4 class="mb-0">Rs. {{ number_format($individualAccount['total_sales'], 0) }}</h4>
                </div>
                <div class="col-md-3">
                    <p class="text-muted mb-1 small">Total Paid</p>
                    <h4 class="mb-0 text-success">Rs. {{ number_format($individualAccount['total_paid'], 0) }}</h4>
                </div>
                <div class="col-md-3">
                    <p class="text-muted mb-1 small">Outstanding</p>
                    <h4 class="mb-0 text-danger">Rs. {{ number_format($individualAccount['outstanding'], 0) }}</h4>
                </div>
                <div class="col-md-3">
                    <p class="text-muted mb-1 small">Sales Count</p>
                    <h4 class="mb-0">{{ $individualAccount['sales_count'] }}</h4>
                </div>
            </div>

            @if($individualAccount['outstanding'] > 0)
            {{-- RECEIVE PAYMENT FORM --}}
            <div class="card bg-light mb-4">
                <div class="card-body">
                    <h6 class="mb-3"><i class="bi bi-cash-coin me-2"></i>Receive Cash Payment</h6>
                    <form id="individualPaymentForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small">Payment Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="amount" step="0.01" min="0.01" max="{{ $individualAccount['outstanding'] }}" required>
                                <small class="text-muted">Max: Rs. {{ number_format($individualAccount['outstanding'], 2) }}</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Reference / Note</label>
                                <input type="text" class="form-control" name="reference" maxlength="100">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check-circle me-1"></i> Receive
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @else
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i><strong>Paid / No Outstanding</strong> - This individual account is fully settled.
            </div>
            @endif

            {{-- PAYMENT HISTORY --}}
            @php
                $paymentHistory = app(\App\Services\CustomerPaymentService::class)->getIndividualPaymentHistory($customer->id);
            @endphp
            
            @if($paymentHistory->count() > 0)
            <h6 class="mb-3 mt-4"><i class="bi bi-clock-history me-2"></i>Payment History</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Invoice</th>
                            <th>Method</th>
                            <th class="text-end">Amount</th>
                            <th>Reference</th>
                            <th>Received By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paymentHistory as $payment)
                        <tr>
                            <td><small>{{ $payment->payment_date->format('M d, Y') }}</small></td>
                            <td><small>{{ $payment->sale->invoice_number }}</small></td>
                            <td><span class="badge bg-secondary">{{ ucfirst($payment->payment_method) }}</span></td>
                            <td class="text-end text-success"><strong>Rs. {{ number_format($payment->amount, 0) }}</strong></td>
                            <td><small>{{ $payment->reference_number ?? '—' }}</small></td>
                            <td><small>{{ $payment->receiver->name ?? 'System' }}</small></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- INDIVIDUAL SALES --}}
            @if($individualAccount['sales_count'] > 0)
            <h6 class="mb-3 mt-4">Individual Sales</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Date</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Outstanding</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($individualAccount['sales'] as $sale)
                        <tr>
                            <td><strong>{{ $sale->invoice_number }}</strong></td>
                            <td><small>{{ $sale->sale_date->format('M d, Y') }}</small></td>
                            <td class="text-end">Rs. {{ number_format($sale->total_amount, 0) }}</td>
                            <td class="text-end text-success">Rs. {{ number_format($sale->paid_amount + $sale->customerPayments->sum('amount'), 0) }}</td>
                            <td class="text-end text-danger">Rs. {{ number_format($sale->current_remaining_udhar, 0) }}</td>
                            <td><span class="badge bg-{{ $sale->current_payment_status === 'paid' ? 'success' : ($sale->current_payment_status === 'partial' ? 'warning' : 'danger') }}">{{ ucfirst($sale->current_payment_status) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- FAMILY ACCOUNTS SECTION --}}
    @if(count($familyAccounts) > 0)
    <div class="card border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-diagram-3 me-2"></i>FAMILY ACCOUNTS (For Reference Only)
                <span class="badge bg-light text-primary ms-2">Family</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Note:</strong> These sales belong to FAMILY accounts. Payments must be made through the family account page.
            </div>

            @foreach($familyAccounts as $familyId => $familyData)
            <div class="mb-3">
                <h6>
                    {{ $familyData['family']->name }} 
                    <a href="{{ route('admin.udhar.show-family', $familyData['family']) }}" class="btn btn-sm btn-outline-primary ms-2">
                        <i class="bi bi-eye"></i> View Family Account
                    </a>
                </h6>
                <p class="text-muted small mb-2">Customer's contribution: Rs. {{ number_format($familyData['outstanding'], 0) }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.getElementById('individualPaymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';
    
    fetch('{{ route("admin.udhar.receive-individual-payment", $customer) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': formData.get('_token'),
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Receive';
        }
    })
    .catch(error => {
        alert('Error submitting payment');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Receive';
    });
});
</script>
@endpush
@endsection
