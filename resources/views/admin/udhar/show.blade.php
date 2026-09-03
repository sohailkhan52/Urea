@extends('layouts.admin')

@section('title', 'Customer Account - ' . $customer->name)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Customer Account</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.udhar.index') }}">Udhar</a></li>
                        <li class="breadcrumb-item active">{{ $customer->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="btn-group">
                <a href="{{ route('admin.customers.statement', $customer) }}" class="btn btn-info">
                    <i class="bi bi-file-text me-1"></i> Full Statement
                </a>
                <a href="{{ route('admin.udhar.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    {{-- Customer Information --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-fill me-2"></i>Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Customer Name</label>
                            <p class="mb-0"><strong>{{ $customer->name }}</strong></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Mobile Number</label>
                            <p class="mb-0">
                                @if($customer->phone)
                                    <i class="bi bi-telephone me-1"></i>{{ $customer->phone }}
                                @else
                                    <span class="text-muted">Not provided</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Family</label>
                            <p class="mb-0">
                                @if($customer->family)
                                    <strong>{{ $customer->family->name }}</strong><br>
                                    <small class="text-muted">{{ $customer->family->family_code }}</small>
                                @else
                                    <span class="text-muted">No family assigned</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Warehouse</label>
                            <p class="mb-0">
                                <i class="bi bi-building me-1"></i>{{ $customer->warehouse->name ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Customer Type</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $customer->type_badge }}">{{ $customer->type_label }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Status</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $customer->status_badge }}">{{ $customer->status_label }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Account Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small d-block">Total Sales</label>
                        <h4 class="mb-0">{{ number_format($statement['summary']['total_sales'], 2) }}</h4>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">Total Paid</label>
                        <h4 class="mb-0 text-success">{{ number_format($statement['summary']['total_payments'], 2) }}</h4>
                    </div>
                    <hr>
                    <div>
                        <label class="text-muted small d-block">Current Udhar</label>
                        <h3 class="mb-0 text-danger">{{ number_format($statement['summary']['current_balance'], 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales List with Payment Options --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-cart-fill me-2"></i>Sales History</h5>
        </div>
        <div class="card-body">
            @if($sales->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-end">Initial Paid</th>
                            <th class="text-end">Additional Payments</th>
                            <th class="text-end">Remaining</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $item)
                        @php
                            $sale = $item['sale'];
                            $remainingUdhar = $item['remaining_udhar'];
                            $paymentStatus = $item['payment_status'];
                            $paymentsCount = $item['payments_count'];
                            
                            $statusBadge = match($paymentStatus) {
                                'paid' => 'success',
                                'partial' => 'warning',
                                'unpaid' => 'danger',
                                default => 'secondary',
                            };
                            
                            $statusLabel = match($paymentStatus) {
                                'paid' => 'PAID',
                                'partial' => 'PARTIAL',
                                'unpaid' => 'UNPAID',
                                default => 'N/A',
                            };
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.sales.show', $sale) }}">
                                    <strong>{{ $sale->invoice_number }}</strong>
                                </a>
                            </td>
                            <td>
                                <small>{{ $sale->sale_date->format('M d, Y') }}</small>
                            </td>
                            <td class="text-end">
                                <strong>{{ number_format($sale->total_amount, 2) }}</strong>
                            </td>
                            <td class="text-end">
                                @if($sale->paid_amount > 0)
                                    <span class="text-success">{{ number_format($sale->paid_amount, 2) }}</span>
                                @else
                                    <span class="text-muted">0.00</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($sale->total_additional_payments > 0)
                                    <span class="text-info">
                                        {{ number_format($sale->total_additional_payments, 2) }}
                                        <small>({{ $paymentsCount }})</small>
                                    </span>
                                @else
                                    <span class="text-muted">0.00</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($remainingUdhar > 0)
                                    <strong class="text-danger">{{ number_format($remainingUdhar, 2) }}</strong>
                                @else
                                    <span class="text-success">0.00</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $statusBadge }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    @if($remainingUdhar > 0)
                                    <button type="button" 
                                            class="btn btn-success receive-payment-btn" 
                                            data-sale-id="{{ $sale->id }}"
                                            data-invoice="{{ $sale->invoice_number }}"
                                            data-remaining="{{ $remainingUdhar }}"
                                            title="Receive Payment">
                                        <i class="bi bi-cash-coin"></i> Pay
                                    </button>
                                    @endif
                                    @if($paymentsCount > 0)
                                    <button type="button" 
                                            class="btn btn-info view-payments-btn" 
                                            data-sale-id="{{ $sale->id }}"
                                            title="View Payment History">
                                        <i class="bi bi-list-ul"></i> History
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2" class="text-end">Totals:</th>
                            <th class="text-end">{{ number_format($sales->sum(fn($s) => $s['sale']->total_amount), 2) }}</th>
                            <th class="text-end">{{ number_format($sales->sum(fn($s) => $s['sale']->paid_amount), 2) }}</th>
                            <th class="text-end">{{ number_format($sales->sum(fn($s) => $s['sale']->total_additional_payments), 2) }}</th>
                            <th class="text-end text-danger">{{ number_format($sales->sum('remaining_udhar'), 2) }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="text-center py-4">
                <i class="bi bi-cart-x fs-1 text-muted d-block mb-3"></i>
                <p class="text-muted">No sales found for this customer.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Transaction History --}}
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Transaction History</h5>
        </div>
        <div class="card-body">
            @if(count($statement['transactions']) > 0)
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Description</th>
                            <th class="text-end">Debit (Sales)</th>
                            <th class="text-end">Credit (Payments)</th>
                            <th class="text-end">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statement['transactions'] as $transaction)
                        <tr>
                            <td>
                                <small>{{ $transaction['date']->format('M d, Y') }}</small>
                            </td>
                            <td>
                                @if($transaction['type'] === 'sale')
                                    <a href="{{ route('admin.sales.show', $transaction['sale']) }}">
                                        {{ $transaction['reference'] }}
                                    </a>
                                @else
                                    {{ $transaction['reference'] }}
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $transaction['type'] === 'sale' ? 'primary' : 'success' }}">
                                    {{ $transaction['description'] }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if($transaction['debit'] > 0)
                                    <span class="text-danger">{{ number_format($transaction['debit'], 2) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($transaction['credit'] > 0)
                                    <span class="text-success">{{ number_format($transaction['credit'], 2) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <strong class="{{ $transaction['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($transaction['balance'], 2) }}
                                </strong>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-4">
                <p class="text-muted">No transactions found.</p>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Receive Cash Payment Modal - Simple Cash Only --}}
<div class="modal fade" id="receivePaymentModal" tabindex="-1" aria-labelledby="receivePaymentLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receivePaymentLabel">Receive Cash Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label"><strong>Invoice:</strong></label>
                    <p id="payment-invoice">---</p>
                </div>
                <div class="mb-3">
                    <label class="form-label"><strong>Remaining Udhar:</strong></label>
                    <p>Rs. <span id="payment-remaining">0.00</span></p>
                </div>
                <div class="mb-3">
                    <label for="payment-amount" class="form-label">Payment Amount <span class="text-danger">*</span></label>
                    <input type="number" 
                           class="form-control" 
                           id="payment-amount" 
                           placeholder="0.00" 
                           step="0.01" 
                           min="0.01">
                </div>
                <div id="payment-error" class="alert alert-danger" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="submit-payment-btn" onclick="submitPayment()">
                    <i class="bi bi-cash-coin me-1"></i>Receive Cash Payment
                </button>
            </div>
        </div>
    </div>
</div>

{{-- View Payments Modal --}}
<div class="modal fade" id="viewPaymentsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="payments-loading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="payments-content" style="display: none;">
                    <div class="alert alert-info">
                        <strong>Sale:</strong> <span id="history-invoice"></span><br>
                        <strong>Total Amount:</strong> Rs. <span id="history-total"></span><br>
                        <strong>Remaining Udhar:</strong> Rs. <span id="history-remaining"></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th>Received By</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody id="payments-table-body">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentSaleId = null;
let currentRemaining = 0;
let receivePaymentModal = null;
let viewPaymentsModal = null;

function viewPaymentHistory(saleId) {
    console.log('viewPaymentHistory called for sale:', saleId);
    
    if (!viewPaymentsModal) {
        console.error('View payments modal not initialized');
        return;
    }
    
    // Show loading
    const loadingDiv = document.getElementById('payments-loading');
    const contentDiv = document.getElementById('payments-content');
    
    if (loadingDiv) loadingDiv.style.display = 'block';
    if (contentDiv) contentDiv.style.display = 'none';
    
    // Show modal
    viewPaymentsModal.show();
    
    // Fetch payment history
    fetch(`/admin/udhar/sales/${saleId}/payments`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Payment history data:', data);
        
        // Populate header info from sale data
        if (data.sale) {
            if (data.sale.invoice_number) document.getElementById('history-invoice').textContent = data.sale.invoice_number;
            if (data.sale.total_amount) document.getElementById('history-total').textContent = data.sale.total_amount;
            if (data.sale.remaining_udhar) document.getElementById('history-remaining').textContent = data.sale.remaining_udhar;
        }
        
        // Populate table
        const tbody = document.getElementById('payments-table-body');
        if (tbody && data.payments && data.payments.length > 0) {
            tbody.innerHTML = data.payments.map(p => `
                <tr>
                    <td>${p.payment_date}</td>
                    <td>Rs. ${p.amount}</td>
                    <td><span class="badge bg-info">${p.payment_method || 'cash'}</span></td>
                    <td>${p.reference_number || '-'}</td>
                    <td>${p.received_by || '-'}</td>
                    <td>${p.notes || '-'}</td>
                </tr>
            `).join('');
        } else if (tbody) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No payments recorded</td></tr>';
        }
        
        // Hide loading, show content
        if (loadingDiv) loadingDiv.style.display = 'none';
        if (contentDiv) contentDiv.style.display = 'block';
    })
    .catch(error => {
        console.error('Error loading payment history:', error);
        if (loadingDiv) loadingDiv.style.display = 'none';
        if (contentDiv) {
            contentDiv.style.display = 'block';
            const msg = `Error loading payment history: ${error.message}`;
            contentDiv.innerHTML = `<div class="alert alert-danger">${msg}</div>`;
        }
    });
}

function submitPayment() {
    console.log('===== submitPayment() START =====');
    
    const amountInput = document.getElementById('payment-amount');
    const errorDiv = document.getElementById('payment-error');
    const submitBtn = document.getElementById('submit-payment-btn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    console.log('Step 1 - Elements found:', {
        amountInput: !!amountInput,
        errorDiv: !!errorDiv,
        submitBtn: !!submitBtn,
        csrfToken: !!csrfToken
    });
    
    if (!amountInput) {
        console.error('FAILED: amountInput not found');
        alert('Error: Amount input not found');
        return;
    }
    
    const amount = parseFloat(amountInput.value);
    console.log('Step 2 - Amount parsed:', amount, 'Type:', typeof amount);
    console.log('Step 3 - Current remaining:', currentRemaining, 'Sale ID:', currentSaleId);
    
    // Validation
    if (!amount || amount <= 0) {
        const msg = 'Payment amount must be greater than zero.';
        console.warn('VALIDATION FAILED: Amount invalid', amount);
        if (errorDiv) {
            errorDiv.textContent = msg;
            errorDiv.style.display = 'block';
        }
        alert(msg);
        return;
    }
    
    if (amount > currentRemaining) {
        const msg = `Payment amount cannot exceed remaining udhar of Rs. ${currentRemaining.toFixed(2)}`;
        console.warn('VALIDATION FAILED: Amount exceeds remaining', {amount, currentRemaining});
        if (errorDiv) {
            errorDiv.textContent = msg;
            errorDiv.style.display = 'block';
        }
        alert(msg);
        return;
    }
    
    console.log('Step 4 - Validations passed');
    
    // Disable button and show loading
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';
        console.log('Step 5a - Button disabled and loading shown');
    } else {
        console.warn('Step 5a - Submit button not found');
    }
    
    if (errorDiv) {
        errorDiv.style.display = 'none';
        console.log('Step 5b - Error div hidden');
    }
    
    console.log('Step 6 - Preparing fetch request');
    console.log('URL:', `/admin/udhar/sales/${currentSaleId}/receive-payment`);
    console.log('CSRF Token:', csrfToken);
    console.log('Payload:', {amount: amount});
    
    // Send payment
    fetch(`/admin/udhar/sales/${currentSaleId}/receive-payment`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ amount: amount })
    })
    .then(response => {
        console.log('Step 7 - Response received:', response.status, response.statusText);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Step 8 - JSON parsed:', data);
        if (data.success) {
            console.log('Step 9 - Success! Showing alert and reloading');
            alert('Payment of Rs. ' + amount.toFixed(2) + ' received successfully!');
            if (receivePaymentModal) receivePaymentModal.hide();
            setTimeout(() => {
                console.log('Step 10 - Reloading page');
                window.location.reload();
            }, 500);
        } else {
            throw new Error(data.message || 'Payment failed');
        }
    })
    .catch(error => {
        console.error('Step ERROR - Catch block:', error);
        const msg = 'Error: ' + error.message;
        if (errorDiv) {
            errorDiv.textContent = msg;
            errorDiv.style.display = 'block';
        } else {
            alert(msg);
        }
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-cash-coin me-1"></i>Receive Cash Payment';
        }
    });
    
    console.log('===== submitPayment() END (fetch sent) =====');
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, initializing payment system');
    
    // Initialize modals
    const receivePaymentModalEl = document.getElementById('receivePaymentModal');
    const viewPaymentsModalEl = document.getElementById('viewPaymentsModal');
    
    if (receivePaymentModalEl) {
        receivePaymentModal = new bootstrap.Modal(receivePaymentModalEl);
        console.log('Payment modal initialized');
    } else {
        console.error('Payment modal not found!');
    }
    
    if (viewPaymentsModalEl) {
        viewPaymentsModal = new bootstrap.Modal(viewPaymentsModalEl);
        console.log('View payments modal initialized');
    } else {
        console.warn('View payments modal not found');
    }
    
    // Attach click handlers to pay buttons
    document.querySelectorAll('.receive-payment-btn').forEach((button, index) => {
        button.addEventListener('click', function() {
            currentSaleId = this.dataset.saleId;
            currentRemaining = parseFloat(this.dataset.remaining);
            
            console.log('Pay button #' + index + ' clicked', {saleId: currentSaleId, remaining: currentRemaining});
            
            // Populate modal fields
            const invoiceEl = document.getElementById('payment-invoice');
            const remainingEl = document.getElementById('payment-remaining');
            const amountEl = document.getElementById('payment-amount');
            const errorEl = document.getElementById('payment-error');
            
            if (invoiceEl) invoiceEl.textContent = this.dataset.invoice;
            if (remainingEl) remainingEl.textContent = currentRemaining.toFixed(2);
            if (amountEl) {
                amountEl.value = '';
                amountEl.max = currentRemaining;
            }
            if (errorEl) errorEl.style.display = 'none';
            
            // Show modal
            if (receivePaymentModal) receivePaymentModal.show();
            
            // Focus on amount input
            setTimeout(() => {
                const input = document.getElementById('payment-amount');
                if (input) input.focus();
            }, 100);
        });
    });
    
    // Attach click handlers to view payment history buttons
    document.querySelectorAll('.view-payments-btn').forEach((button, index) => {
        button.addEventListener('click', function() {
            const saleId = this.dataset.saleId;
            console.log('History button #' + index + ' clicked', {saleId: saleId});
            viewPaymentHistory(saleId);
        });
    });
});
</script>
@endpush
