@extends('layouts.admin')

@section('title', 'Family Account - ' . $family->name)

@php
    $company = \App\Models\Company::active()->first();
@endphp

@push('styles')
<style>
    .family-print-details {
        display: none;
    }

    .family-print-header,
    .family-print-footer {
        display: none;
    }

    @media print {
        @page { size: A4 landscape; margin: 8mm; }

        .sidebar,
        .topbar,
        .no-print {
            display: none !important;
        }

        .content {
            margin-left: 0 !important;
            padding: 0 !important;
        }

        .family-print-header {
            display: flex !important;
            align-items: flex-start;
            justify-content: space-between;
            padding-bottom: 12px;
            border-bottom: 3px solid #000;
            margin-bottom: 20px;
            font-size: 11px;
            line-height: 1.4;
        }

        .family-print-header .company-name,
        .family-print-header .print-title {
            font-size: 24px;
            font-weight: 800;
            line-height: 1.2;
        }

        .family-print-header .print-title-block {
            text-align: right;
        }

        .family-print-header .print-title-block small {
            display: block;
            font-size: 11px;
            font-weight: 400;
        }

        .family-print-footer {
            display: block !important;
            position: fixed;
            right: 0;
            bottom: 0;
            left: 0;
            padding-top: 6px;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 11px;
            color: #000 !important;
        }

        .container-fluid {
            max-width: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .table-responsive {
            overflow-x: visible !important;
        }

        .family-ledger-table {
            width: 100% !important;
            min-width: 0 !important;
            table-layout: auto !important;
            font-size: 11px !important;
        }

        .family-print-details {
            display: block !important;
            width: 62%;
            margin: 0 auto 18px;
            font-size: 11px;
            line-height: 1.5;
        }

        .family-print-detail-row {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 18px;
            min-height: 16px;
        }

        .family-print-detail-row strong {
            font-weight: 700;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="family-print-header">
        <div>
            <div class="company-name">{{ $company?->name ?? 'DeraNexa' }}</div>
            <div>{{ implode(' / ', array_filter([$company?->phone ?: '03239123800', $company?->additional_number])) }}</div>
        </div>
        <div class="print-title-block">
            <div class="print-title">Family Account</div>
            <small>Family #: {{ $family->family_code ?? $family->id }}</small>
            <small>Date: {{ now()->format('d M Y') }}</small>
        </div>
    </div>

    <div class="family-print-details">
        <div class="family-print-detail-row">
            <span>Family</span>
            <strong>{{ $family->name ?: '-' }}</strong>
        </div>
        <div class="family-print-detail-row">
            <span>Family Code</span>
            <strong>{{ $family->family_code ?? $family->id }}</strong>
        </div>
        <div class="family-print-detail-row">
            <span>Members</span>
            <strong>{{ $familyAccount['members_count'] }}</strong>
        </div>
    </div>

    <div class="mb-4 no-print">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">{{ $family->name }}</h1>
                <p class="text-muted mb-0">Family Account</p>
            </div>
            <div class="d-flex gap-2 no-print">
                <button type="button" class="btn btn-primary" onclick="window.print()" title="Print family account">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
                <a href="{{ route('admin.udhar.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    {{-- FAMILY ACCOUNT SUMMARY --}}
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-diagram-3 me-2"></i>FAMILY ACCOUNT
                    <span class="badge bg-light text-primary ms-2">Family</span>
                </h5>
                @if($familyAccount['outstanding'] != 0)
                <button type="button" class="btn btn-success no-print" data-bs-toggle="modal" data-bs-target="#familyPaymentModal">
                    <i class="bi bi-cash-coin me-1"></i>
                    {{ $familyAccount['outstanding'] > 0 ? 'Record Cash Payment' : 'Adjust Payment' }}
                </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4 no-print">
                <div class="col-md-2">
                    <p class="text-muted mb-1 small">Family Members</p>
                    <h4 class="mb-0">{{ $familyAccount['members_count'] }}</h4>
                </div>
                <div class="col-md-2">
                    <p class="text-muted mb-1 small">Total Sales</p>
                    <h4 class="mb-0">Rs. {{ number_format($familyAccount['total_sales'], 0) }}</h4>
                </div>
                <div class="col-md-2">
                    <p class="text-muted mb-1 small">Total Returns</p>
                    <h4 class="mb-0 text-warning">Rs. {{ number_format($familyAccount['total_returns'], 0) }}</h4>
                </div>
                <div class="col-md-2">
                    <p class="text-muted mb-1 small">Total Paid</p>
                    <h4 class="mb-0 text-success">Rs. {{ number_format($familyAccount['total_paid'], 0) }}</h4>
                </div>
                <div class="col-md-2">
                    <p class="text-muted mb-1 small">Outstanding Family Udhar</p>
                    <h4 class="mb-0 text-danger">Rs. {{ number_format($familyAccount['outstanding'], 0) }}</h4>
                </div>
                <div class="col-md-2">
                    <p class="text-muted mb-1 small">Sales Count</p>
                    <h4 class="mb-0">{{ $familyAccount['sales_count'] }}</h4>
                </div>
            </div>

            {{-- FAMILY PAYMENT HISTORY --}}
            @php
                $paymentHistory = app(\App\Services\CustomerPaymentService::class)->getFamilyPaymentHistory($family->id);
            @endphp
            
            @if($paymentHistory->count() > 0)
            <button type="button" class="btn btn-outline-primary mt-3 mb-3 no-print" id="toggleFamilyPaymentHistory" aria-expanded="false" aria-controls="familyPaymentHistory">
                <i class="bi bi-clock-history me-1"></i> Family Payment History
            </button>
            <div class="table-responsive" id="familyPaymentHistory" style="display: none;">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Customer</th>
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
                            <td><strong>{{ $payment->customer->name }}</strong></td>
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

            {{-- FAMILY TRANSACTION LEDGER --}}
            @if($familyAccount['sales_count'] > 0)
            <h6 class="mb-3 mt-4"><i class="bi bi-journal-text me-2"></i>Family Transaction Ledger</h6>
            <div class="table-responsive">
                <table class="table table-sm family-ledger-table">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Invoice/Reference</th>
                            <th>Price</th>
                            <th>Returns</th>
                            <th>Total Paid</th>
                            <th>Outstanding</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $transactions = collect();
                            
                            // Add sales to transactions
                            foreach($familyAccount['sales'] as $sale) {
                                // Use model attribute which correctly excludes return_adjustment and return_credit
                                $salePaid = $sale->paid_amount + $sale->total_additional_payments;
                                
                                $transactions->push([
                                    'date' => $sale->sale_date,
                                    'customer' => $sale->customer->name,
                                    'reference' => $sale->invoice_number,
                                    'amount' => $sale->total_amount,
                                    'returns' => $sale->total_returned_amount,
                                    'paid' => $salePaid,
                                ]);
                            }
                            
                            $transactions = $transactions->sortBy('date');
                        @endphp
                        
                        @foreach($transactions as $transaction)
                        @php
                            $outstanding = $transaction['amount'] - $transaction['returns'] - $transaction['paid'];
                        @endphp
                        <tr>
                            <td><small>{{ \Carbon\Carbon::parse($transaction['date'])->format('M d, Y') }}</small></td>
                            <td><strong>{{ $transaction['customer'] }}</strong></td>
                            <td><small>{{ $transaction['reference'] }}</small></td>
                            <td class="text-danger">
                                Rs. {{ number_format($transaction['amount'], 0) }}
                            </td>
                            <td class="text-warning">
                                {{ $transaction['returns'] > 0 ? 'Rs. ' . number_format($transaction['returns'], 0) : '—' }}
                            </td>
                            <td class="text-success">
                                {{ $transaction['paid'] > 0 ? 'Rs. ' . number_format($transaction['paid'], 0) : '—' }}
                            </td>
                            <td>
                                <strong class="text-{{ $outstanding > 0 ? 'danger' : 'success' }}">
                                    Rs. {{ number_format($outstanding, 0) }}
                                </strong>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="ledger-total-spacer">
                            <td colspan="8" style="height: 20px; padding: 0; background: #fff; border: 0;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td class="text-start"><strong>Totals:</strong></td>
                            <td><strong class="text-danger">Rs. {{ number_format($familyAccount['total_sales'], 0) }}</strong></td>
                            <td><strong class="text-warning">Rs. {{ number_format($familyAccount['total_returns'], 0) }}</strong></td>
                            <td><strong class="text-success">Rs. {{ number_format($familyAccount['total_paid'], 0) }}</strong></td>
                            <td>
                                <strong class="text-danger">Total Udhar: Rs. {{ number_format($familyAccount['outstanding'], 0) }}</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif
        </div>
    </div>

    <div class="family-print-footer">
        Address: {{ $company?->address ?: 'Naivela Dera Ismail Khan' }}
    </div>
</div>

<div class="modal fade" id="familyPaymentModal" tabindex="-1" aria-labelledby="familyPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="familyPaymentModalLabel">{{ $familyAccount['outstanding'] > 0 ? 'Record Cash Payment' : 'Adjust Payment' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="familyCashPaymentForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Family</label><input type="text" class="form-control" value="{{ $family->name }}" readonly></div>
                    <div class="mb-3"><label class="form-label">Outstanding Amount</label><input type="text" class="form-control" value="Rs. {{ number_format($familyAccount['outstanding'], 0) }}" readonly></div>
                    <div class="mb-3"><label class="form-label">{{ $familyAccount['outstanding'] > 0 ? 'Payment Amount' : 'Adjustment Amount' }} <span class="text-danger">*</span></label><input type="number" class="form-control" name="amount" step="0.01" min="0.01" max="{{ abs($familyAccount['outstanding']) }}" value="{{ $familyAccount['outstanding'] < 0 ? abs($familyAccount['outstanding']) : '' }}" {{ $familyAccount['outstanding'] < 0 ? 'readonly' : '' }} required><small class="text-muted">{{ $familyAccount['outstanding'] > 0 ? 'Max' : 'Exact adjustment' }}: Rs. {{ number_format(abs($familyAccount['outstanding']), 0) }}</small></div>
                    <input type="hidden" name="payment_date" value="{{ date('Y-m-d') }}">
                    @if($familyAccount['outstanding'] > 0)<input type="hidden" name="allocation_type" value="auto">@endif
                    <div class="mb-3"><label class="form-label">Reference (Optional)</label><input type="text" class="form-control" name="reference" maxlength="100" placeholder="Transaction ID, etc."></div>
                    <div><label class="form-label">Notes (Optional)</label><textarea name="notes" class="form-control" rows="2" placeholder="Add any notes..."></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i> {{ $familyAccount['outstanding'] > 0 ? 'Record Payment' : 'Adjust to Zero' }}</button></div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('toggleFamilyPaymentHistory')?.addEventListener('click', function() {
    const history = document.getElementById('familyPaymentHistory');
    const isHidden = history.style.display === 'none';

    history.style.display = isHidden ? 'block' : 'none';
    this.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
});

document.getElementById('familyCashPaymentForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;

    fetch('{{ $familyAccount['outstanding'] > 0 ? route("admin.udhar.receive-family-payment", $family) : route("admin.udhar.adjust-family-payment", $family) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
            'Accept': 'application/json'
        },
        body: new FormData(form)
    }).then(response => response.json()).then(data => {
        if (!data.success) throw new Error(data.message || 'Payment failed');
        window.location.reload();
    }).catch(error => {
        alert(error.message);
        submitBtn.disabled = false;
    });
});

if (document.getElementById('familyPaymentForm')) {
// Toggle manual allocation section
document.querySelectorAll('input[name="allocation_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const manualSection = document.getElementById('manualAllocationSection');
        if (this.value === 'manual') {
            manualSection.style.display = 'block';
        } else {
            manualSection.style.display = 'none';
            // Reset allocation inputs
            document.querySelectorAll('.allocation-input').forEach(input => {
                input.value = '0';
            });
            updateTotalAllocated();
        }
    });
});

// Calculate total allocated amount
function updateTotalAllocated() {
    let total = 0;
    document.querySelectorAll('.allocation-input').forEach(input => {
        const value = parseFloat(input.value) || 0;
        total += value;
    });
    
    document.getElementById('totalAllocated').textContent = 'Rs. ' + total.toFixed(2);
    
    // Validate against payment amount
    const paymentAmount = parseFloat(document.getElementById('paymentAmount').value) || 0;
    const errorDiv = document.getElementById('allocationError');
    
    if (Math.abs(total - paymentAmount) > 0.01 && total > 0) {
        errorDiv.textContent = 'Must equal Rs. ' + paymentAmount.toFixed(2);
        errorDiv.style.display = 'block';
    } else {
        errorDiv.style.display = 'none';
    }
}

// Update total when allocation inputs change
document.querySelectorAll('.allocation-input').forEach(input => {
    input.addEventListener('input', function() {
        const max = parseFloat(this.dataset.max);
        if (parseFloat(this.value) > max) {
            this.value = max;
        }
        updateTotalAllocated();
    });
});

// Update validation when payment amount changes
document.getElementById('paymentAmount').addEventListener('input', updateTotalAllocated);

// Form submission
document.getElementById('familyPaymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const allocationType = document.querySelector('input[name="allocation_type"]:checked').value;
    const formData = new FormData(this);
    
    // Validation for manual allocation
    if (allocationType === 'manual') {
        const paymentAmount = parseFloat(formData.get('amount'));
        let totalAllocated = 0;
        
        // Calculate total and filter out zero allocations
        const allocationData = [];
        document.querySelectorAll('.allocation-input').forEach((input, index) => {
            const amount = parseFloat(input.value) || 0;
            if (amount > 0) {
                totalAllocated += amount;
                const saleId = input.dataset.saleId;
                allocationData.push({ sale_id: parseInt(saleId), amount: amount });
            }
        });
        
        if (Math.abs(totalAllocated - paymentAmount) > 0.01) {
            alert('Error: Total allocated amount (Rs. ' + totalAllocated.toFixed(2) + ') must equal payment amount (Rs. ' + paymentAmount.toFixed(2) + ')');
            return;
        }
        
        if (allocationData.length === 0) {
            alert('Error: Please allocate payment to at least one sale');
            return;
        }
    }
    
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';
    
    fetch('{{ route("admin.udhar.receive-family-payment", $family) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': formData.get('_token'),
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            amount: formData.get('amount'),
            payment_date: formData.get('payment_date'),
            allocation_type: formData.get('allocation_type'),
            allocation: allocationType === 'manual' ? Array.from(document.querySelectorAll('.allocation-input'))
                .filter(input => parseFloat(input.value) > 0)
                .map(input => ({
                    sale_id: parseInt(input.dataset.saleId),
                    amount: parseFloat(input.value)
                })) : [],
            reference: formData.get('reference'),
            notes: ''
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Receive Family Payment';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error submitting payment');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Receive Family Payment';
    });
});
}
</script>
@endpush
@endsection
