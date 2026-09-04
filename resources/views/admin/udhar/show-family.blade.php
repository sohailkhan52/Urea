@extends('layouts.admin')

@section('title', 'Family Account - ' . $family->name)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">{{ $family->name }}</h1>
                <p class="text-muted mb-0">Family Account</p>
            </div>
            <a href="{{ route('admin.udhar.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    {{-- FAMILY ACCOUNT SUMMARY --}}
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-diagram-3 me-2"></i>FAMILY ACCOUNT
                <span class="badge bg-light text-primary ms-2">Family</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-2">
                    <p class="text-muted mb-1 small">Family Members</p>
                    <h4 class="mb-0">{{ $familyAccount['members_count'] }}</h4>
                </div>
                <div class="col-md-2">
                    <p class="text-muted mb-1 small">Total Sales</p>
                    <h4 class="mb-0">Rs. {{ number_format($familyAccount['total_sales'], 0) }}</h4>
                </div>
                <div class="col-md-3">
                    <p class="text-muted mb-1 small">Total Paid</p>
                    <h4 class="mb-0 text-success">Rs. {{ number_format($familyAccount['total_paid'], 0) }}</h4>
                </div>
                <div class="col-md-3">
                    <p class="text-muted mb-1 small">Outstanding Family Udhar</p>
                    <h4 class="mb-0 text-danger">Rs. {{ number_format($familyAccount['outstanding'], 0) }}</h4>
                </div>
                <div class="col-md-2">
                    <p class="text-muted mb-1 small">Sales Count</p>
                    <h4 class="mb-0">{{ $familyAccount['sales_count'] }}</h4>
                </div>
            </div>

            {{-- FAMILY MEMBERS LIST --}}
            <h6 class="mb-3"><i class="bi bi-people me-2"></i>Family Members</h6>
            <div class="row g-2 mb-4">
                @foreach($familyAccount['members'] as $member)
                <div class="col-md-3">
                    <div class="border rounded p-2 bg-light">
                        <strong>{{ $member->name }}</strong><br>
                        <small class="text-muted">{{ $member->phone }}</small>
                    </div>
                </div>
                @endforeach
            </div>

            @if($familyAccount['outstanding'] > 0)
            {{-- RECEIVE FAMILY PAYMENT FORM --}}
            <div class="card bg-light mb-4">
                <div class="card-body">
                    <h6 class="mb-3"><i class="bi bi-cash-coin me-2"></i>Receive Family Cash Payment</h6>
                    <form id="familyPaymentForm">
                        @csrf
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label small">Payment Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="amount" id="paymentAmount" step="0.01" min="0.01" max="{{ $familyAccount['outstanding'] }}" required>
                                <small class="text-muted">Max: Rs. {{ number_format($familyAccount['outstanding'], 2) }}</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Reference / Note</label>
                                <input type="text" class="form-control" name="reference" maxlength="100">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold">Apply Payment To: <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="allocation_type" id="allocAuto" value="auto" checked>
                                    <label class="form-check-label" for="allocAuto">
                                        <strong>Oldest Outstanding Transactions</strong> (Automatic)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="allocation_type" id="allocManual" value="manual">
                                    <label class="form-check-label" for="allocManual">
                                        <strong>Select Customer / Transactions Manually</strong>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- MANUAL ALLOCATION SECTION --}}
                        <div id="manualAllocationSection" style="display: none;">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Manual Allocation</h6>
                                </div>
                                <div class="card-body">
                                    <p class="small text-muted mb-3">Allocate the payment amount across outstanding family sales. Total must equal payment amount.</p>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Customer</th>
                                                    <th>Invoice</th>
                                                    <th>Date</th>
                                                    <th class="text-end">Outstanding</th>
                                                    <th style="width: 150px;">Allocate Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($familyAccount['outstanding_sales'] as $sale)
                                                <tr>
                                                    <td><strong>{{ $sale->customer->name }}</strong></td>
                                                    <td>{{ $sale->invoice_number }}</td>
                                                    <td><small>{{ $sale->sale_date->format('M d, Y') }}</small></td>
                                                    <td class="text-end text-danger">Rs. {{ number_format($sale->current_remaining_udhar, 2) }}</td>
                                                    <td>
                                                        <input type="number" 
                                                               class="form-control form-control-sm allocation-input" 
                                                               name="allocation[{{ $loop->index }}][amount]"
                                                               data-sale-id="{{ $sale->id }}"
                                                               data-max="{{ $sale->current_remaining_udhar }}"
                                                               step="0.01" 
                                                               min="0" 
                                                               max="{{ $sale->current_remaining_udhar }}"
                                                               value="0">
                                                        <input type="hidden" name="allocation[{{ $loop->index }}][sale_id]" value="{{ $sale->id }}">
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <td colspan="4" class="text-end"><strong>Total Allocated:</strong></td>
                                                    <td>
                                                        <strong id="totalAllocated" class="text-primary">Rs. 0.00</strong>
                                                        <div id="allocationError" class="text-danger small" style="display: none;"></div>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i> Receive Family Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @else
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i><strong>Paid / No Outstanding</strong> - This family account is fully settled.
            </div>
            @endif

            {{-- FAMILY PAYMENT HISTORY --}}
            @php
                $paymentHistory = app(\App\Services\CustomerPaymentService::class)->getFamilyPaymentHistory($family->id);
            @endphp
            
            @if($paymentHistory->count() > 0)
            <h6 class="mb-3 mt-4"><i class="bi bi-clock-history me-2"></i>Family Payment History</h6>
            <div class="table-responsive">
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
                <table class="table table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Invoice/Reference</th>
                            <th>Transaction Type</th>
                            <th class="text-end">Debit (+)</th>
                            <th class="text-end">Credit (-)</th>
                            <th class="text-end">Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $runningBalance = 0;
                            $transactions = collect();
                            
                            // Add sales to transactions
                            foreach($familyAccount['sales'] as $sale) {
                                $transactions->push([
                                    'date' => $sale->sale_date,
                                    'customer' => $sale->customer->name,
                                    'reference' => $sale->invoice_number,
                                    'type' => 'Sale',
                                    'debit' => $sale->total_amount,
                                    'credit' => 0,
                                    'status' => ucfirst($sale->current_payment_status)
                                ]);
                            }
                            
                            // Add payments to transactions
                            foreach($paymentHistory as $payment) {
                                $transactions->push([
                                    'date' => $payment->payment_date,
                                    'customer' => $payment->customer->name,
                                    'reference' => $payment->sale->invoice_number . ' (Payment)',
                                    'type' => 'Payment',
                                    'debit' => 0,
                                    'credit' => $payment->amount,
                                    'status' => 'Received'
                                ]);
                            }
                            
                            $transactions = $transactions->sortBy('date');
                        @endphp
                        
                        @foreach($transactions as $transaction)
                        @php
                            $runningBalance += $transaction['debit'] - $transaction['credit'];
                        @endphp
                        <tr>
                            <td><small>{{ \Carbon\Carbon::parse($transaction['date'])->format('M d, Y') }}</small></td>
                            <td><strong>{{ $transaction['customer'] }}</strong></td>
                            <td><small>{{ $transaction['reference'] }}</small></td>
                            <td>
                                <span class="badge bg-{{ $transaction['type'] === 'Sale' ? 'danger' : 'success' }}">
                                    {{ $transaction['type'] }}
                                </span>
                            </td>
                            <td class="text-end text-danger">
                                {{ $transaction['debit'] > 0 ? 'Rs. ' . number_format($transaction['debit'], 0) : '—' }}
                            </td>
                            <td class="text-end text-success">
                                {{ $transaction['credit'] > 0 ? 'Rs. ' . number_format($transaction['credit'], 0) : '—' }}
                            </td>
                            <td class="text-end">
                                <strong class="text-{{ $runningBalance > 0 ? 'danger' : 'success' }}">
                                    Rs. {{ number_format($runningBalance, 0) }}
                                </strong>
                            </td>
                            <td><small>{{ $transaction['status'] }}</small></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="6" class="text-end"><strong>Current Balance:</strong></td>
                            <td class="text-end">
                                <strong class="text-danger">Rs. {{ number_format($familyAccount['outstanding'], 0) }}</strong>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
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
</script>
@endpush
@endsection
