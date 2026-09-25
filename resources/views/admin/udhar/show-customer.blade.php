@extends('layouts.admin')

@section('title', 'Customer Account - ' . $customer->name)

@php
    $company = \App\Models\Company::active()->first();
@endphp

@push('styles')
<style>
    .customer-print-header,
    .customer-print-footer {
        display: none;
    }

    .customer-print-details {
        display: none;
    }

    @media print {
        @page { size: 165mm 210mm; margin: 8mm; }

        .sidebar,
        .topbar,
        .no-print {
            display: none !important;
        }

        .content {
            margin-left: 0 !important;
            padding: 0 !important;
        }

        .customer-print-header {
            display: flex !important;
            align-items: flex-start;
            justify-content: space-between;
            padding-bottom: 12px;
            border-bottom: 3px solid #000;
            margin-bottom: 20px;
            font-size: 11px;
            line-height: 1.4;
        }

        .customer-print-header .company-name,
        .customer-print-header .print-title {
            font-size: 24px;
            font-weight: 800;
            line-height: 1.2;
        }

        .customer-print-header .print-title-block {
            text-align: right;
        }

        .customer-print-header .print-title-block small {
            display: block;
            font-size: 11px;
            font-weight: 400;
        }

        .customer-print-footer {
            display: block !important;
            position: fixed;
            right: auto;
            bottom: 0;
            left: 0;
            width: 100%;
            padding-top: 6px;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 11px;
            color: #000 !important;
        }

        .customer-print-details {
            display: block !important;
            width: 34%;
            margin: 0 auto 18px;
            font-size: 11px;
            line-height: 1.5;
        }

        .customer-print-detail-row {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 18px;
            min-height: 16px;
        }

        .customer-print-detail-row strong {
            font-weight: 700;
        }

        .container-fluid {
            width: 100% !important;
            max-width: none !important;
            padding: 0 !important;
            margin: 0 auto !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="customer-print-header">
        <div>
            <div class="company-name">{{ $company?->name ?? 'DeraNexa' }}</div>
            <div>{{ implode(' / ', array_filter([$company?->phone ?: '03239123800', $company?->additional_number])) }}</div>
        </div>
        <div class="print-title-block">
            <div class="print-title">Customer Account</div>
            <small>Customer: {{ $customer->name }}</small>
            <small>Date: {{ now()->format('d M Y') }}</small>
        </div>
    </div>

    <div class="customer-print-details">
        <div class="customer-print-detail-row">
            <span>Customer</span>
            <strong>{{ $customer->name }}</strong>
        </div>
        <div class="customer-print-detail-row">
            <span>Family</span>
            <strong>{{ $customer->family?->name ?? '-' }}</strong>
        </div>
        <div class="customer-print-detail-row">
            <span>Phone</span>
            <strong>{{ $customer->phone ?: '-' }}</strong>
        </div>
    </div>

    <div class="mb-4 no-print">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">{{ $customer->name }}</h1>
                <p class="text-muted mb-0">Individual Customer Account</p>
            </div>
            <button type="button" class="btn btn-primary no-print" onclick="window.print()" title="Print customer account">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    {{-- INDIVIDUAL ACCOUNT --}}
    <div class="card mb-4 border-info">
        <div class="card-header bg-info text-white no-print">
            <h5 class="mb-0">
                <i class="bi bi-person-circle me-2"></i>INDIVIDUAL ACCOUNT
                <span class="badge bg-light text-info ms-2">Individual</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="row mb-4 no-print">
                <div class="col-md-3">
                    <p class="text-muted mb-1 small">Total Sales</p>
                    <h4 class="mb-0">Rs. {{ number_format($individualAccount['total_sales'], 0) }}</h4>
                </div>
                <div class="col-md-3">
                    <p class="text-muted mb-1 small">Total Paid</p>
                    <h4 class="mb-0 text-success">Rs. {{ number_format($individualAccount['total_paid'], 0) }}</h4>
                </div>
                <div class="col-md-2">
                    <p class="text-muted mb-1 small">Total Returns</p>
                    <h4 class="mb-0 text-info">Rs. {{ number_format($individualAccount['total_returns'] ?? 0, 0) }}</h4>
                </div>
                <div class="col-md-2">
                    <p class="text-muted mb-1 small">Outstanding</p>
                    <h4 class="mb-0 text-danger">Rs. {{ number_format($individualAccount['outstanding'], 0) }}</h4>
                </div>
                <div class="col-md-2">
                    <p class="text-muted mb-1 small">Sales Count</p>
                    <h4 class="mb-0">{{ $individualAccount['sales_count'] }}</h4>
                </div>
            </div>

            @if($individualAccount['outstanding'] > 0)
            <div class="mb-4 no-print">
                <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#individualPaymentModal">
                    <i class="bi bi-cash-coin me-2"></i> Record Cash Payment
                </button>
            </div>
            @elseif($individualAccount['outstanding'] < 0)
            <div class="mb-4 no-print">
                <button type="button" class="btn btn-warning btn-lg" data-bs-toggle="modal" data-bs-target="#individualRefundModal">
                    <i class="bi bi-arrow-counterclockwise me-2"></i> Refund Credit
                </button>
            </div>
            @else
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
                            <td class="text-success"><strong>Rs. {{ number_format($payment->amount, 0) }}</strong></td>
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
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Returns</th>
                            <th>Outstanding</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($individualAccount['sales'] as $sale)
                        @php
                            // Use model attribute which correctly excludes return_adjustment and return_credit
                            $outstanding = $sale->current_remaining_udhar;
                            $totalPaid = $sale->paid_amount + $sale->total_additional_payments;
                        @endphp
                        <tr>
                            <td><strong>{{ $sale->invoice_number }}</strong></td>
                            <td><small>{{ $sale->sale_date->format('M d, Y') }}</small></td>
                            <td>Rs. {{ number_format($sale->total_amount, 0) }}</td>
                            <td class="text-success">Rs. {{ number_format($totalPaid, 0) }}</td>
                            <td>Rs. {{ number_format($sale->total_returned_amount, 0) }}</td>
                            <td class="{{ $outstanding < 0 ? 'text-success' : 'text-danger' }}">Rs. {{ number_format($outstanding, 0) }}</td>
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

    <div class="customer-print-footer">
        Address: {{ $company?->address ?: 'Naivela Dera Ismail Khan' }}
    </div>
</div>

<div class="modal fade" id="individualPaymentModal" tabindex="-1" aria-labelledby="individualPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="individualPaymentModalLabel">Record Cash Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="individualPaymentForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Customer</label>
                        <input type="text" class="form-control" value="{{ $customer->name }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Outstanding Amount</label>
                        <input type="text" class="form-control" value="Rs. {{ number_format($individualAccount['outstanding'], 0) }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="amount" step="0.01" min="0.01" max="{{ $individualAccount['outstanding'] }}" required>
                        <small class="text-muted">Max: Rs. {{ number_format($individualAccount['outstanding'], 0) }}</small>
                    </div>
                    <input type="hidden" name="payment_date" value="{{ date('Y-m-d') }}">
                    <div class="mb-3">
                        <label class="form-label">Reference (Optional)</label>
                        <input type="text" class="form-control" name="reference" maxlength="100" placeholder="Transaction ID, etc.">
                    </div>
                    <div>
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Add any notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="individualRefundModal" tabindex="-1" aria-labelledby="individualRefundModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="individualRefundModalLabel">Refund Customer Credit</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form id="individualRefundForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Customer</label><input type="text" class="form-control" value="{{ $customer->name }}" readonly></div>
                    <div class="mb-3"><label class="form-label">Available Credit</label><input type="text" class="form-control" value="Rs. {{ number_format(abs($individualAccount['outstanding']), 0) }}" readonly></div>
                    <div class="mb-3"><label class="form-label">Refund Amount <span class="text-danger">*</span></label><input type="number" class="form-control" name="amount" step="0.01" min="0.01" max="{{ abs($individualAccount['outstanding']) }}" required></div>
                    <input type="hidden" name="payment_date" value="{{ date('Y-m-d') }}">
                    <div class="mb-3"><label class="form-label">Reference (Optional)</label><input type="text" class="form-control" name="reference" maxlength="100" placeholder="Transaction ID, etc."></div>
                    <div><label class="form-label">Notes (Optional)</label><textarea name="notes" class="form-control" rows="2" placeholder="Add any notes..."></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-warning"><i class="bi bi-check-circle me-1"></i> Record Refund</button></div>
            </form>
        </div>
    </div>
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

document.getElementById('individualRefundForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    fetch('{{ route("admin.udhar.refund-individual-payment", $customer) }}', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value, 'Accept': 'application/json'},
        body: new FormData(form)
    }).then(response => response.json()).then(data => {
        if (!data.success) throw new Error(data.message || 'Refund failed');
        window.location.reload();
    }).catch(error => {
        alert(error.message);
        submitBtn.disabled = false;
    });
});
</script>
@endpush
@endsection
