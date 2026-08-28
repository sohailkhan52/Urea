@extends('layouts.admin')

@section('title', 'Sales Return Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Sales Return: {{ $return->return_number }}</h1>
        <div class="btn-group">
            @if($return->status === 'confirmed')
            <a href="{{ route('admin.sales.returns.print', $return) }}" class="btn btn-primary" target="_blank">
                <i class="bi bi-printer me-1"></i> Print
            </a>
            @endif
            <a href="{{ route('admin.sales.returns.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Returns
            </a>
        </div>
    </div>

    {{-- Status and Actions --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex gap-3">
                        <div>
                            <strong>Status:</strong>
                            @if($return->status === 'draft')
                                <span class="badge bg-secondary">Draft</span>
                            @elseif($return->status === 'confirmed')
                                <span class="badge bg-success">Confirmed</span>
                            @elseif($return->status === 'cancelled')
                                <span class="badge bg-danger">Cancelled</span>
                            @endif
                        </div>
                        <div>
                            <strong>Payment Status:</strong>
                            @if($return->payment_status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($return->payment_status === 'refunded')
                                <span class="badge bg-success">Refunded</span>
                            @elseif($return->payment_status === 'credited')
                                <span class="badge bg-info">Credited</span>
                            @elseif($return->payment_status === 'partial')
                                <span class="badge bg-secondary">Partial</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    @if($return->status === 'draft' && auth()->user()->can('sales.approve'))
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmModal">
                        <i class="bi bi-check-circle me-1"></i> Confirm Return
                    </button>
                    @endif
                    @if($return->status === 'confirmed' && auth()->user()->can('sales.cancel'))
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                        <i class="bi bi-x-circle me-1"></i> Cancel Return
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            {{-- Return Information --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Return Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Return Number:</th>
                            <td>{{ $return->return_number }}</td>
                        </tr>
                        <tr>
                            <th>Return Date:</th>
                            <td>{{ $return->return_date->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <th>Original Invoice:</th>
                            <td>
                                <a href="{{ route('admin.sales.show', $return->sale) }}">
                                    {{ $return->sale->invoice_number }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Customer:</th>
                            <td>{{ $return->sale->customer->name }}</td>
                        </tr>
                        <tr>
                            <th>Warehouse:</th>
                            <td>{{ $return->sale->warehouse->name }}</td>
                        </tr>
                        <tr>
                            <th>Created By:</th>
                            <td>{{ $return->creator->name }} on {{ $return->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        @if($return->confirmed_by)
                        <tr>
                            <th>Confirmed By:</th>
                            <td>{{ $return->confirmer->name }} on {{ $return->confirmed_at->format('d M Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($return->cancelled_by)
                        <tr>
                            <th>Cancelled By:</th>
                            <td>{{ $return->canceller->name }} on {{ $return->cancelled_at->format('d M Y H:i') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            {{-- Financial Summary --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Financial Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Subtotal:</th>
                            <td class="text-end">Rs. {{ number_format($return->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Discount:</th>
                            <td class="text-end">Rs. {{ number_format($return->discount_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th><strong>Total Return Amount:</strong></th>
                            <td class="text-end"><strong>Rs. {{ number_format($return->total_amount, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td colspan="2"><hr></td>
                        </tr>
                        <tr>
                            <th>Refund Amount:</th>
                            <td class="text-end">Rs. {{ number_format($return->refund_amount ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Credit Amount:</th>
                            <td class="text-end">Rs. {{ number_format($return->credit_amount ?? 0, 2) }}</td>
                        </tr>
                        @if($return->refund_method)
                        <tr>
                            <th>Refund Method:</th>
                            <td class="text-end">{{ ucwords(str_replace('_', ' ', $return->refund_method)) }}</td>
                        </tr>
                        @endif
                        @if($return->refund_reference)
                        <tr>
                            <th>Refund Reference:</th>
                            <td class="text-end">{{ $return->refund_reference }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Return Items --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Returned Items</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($return->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td class="text-end">Rs. {{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                            <td class="text-end">Rs. {{ number_format($item->discount, 2) }}</td>
                            <td class="text-end">Rs. {{ number_format($item->total, 2) }}</td>
                        </tr>
                        @if($item->reason)
                        <tr>
                            <td colspan="5" class="bg-light">
                                <small><strong>Reason:</strong> {{ $item->reason }}</small>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Total:</th>
                            <th class="text-end">Rs. {{ number_format($return->total_amount, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Reason and Notes --}}
    @if($return->reason || $return->notes)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Additional Information</h5>
        </div>
        <div class="card-body">
            @if($return->reason)
            <div class="mb-3">
                <strong>Reason:</strong>
                <p>{{ $return->reason }}</p>
            </div>
            @endif
            @if($return->notes)
            <div>
                <strong>Notes:</strong>
                <p>{{ $return->notes }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- Confirm Modal --}}
@if($return->status === 'draft')
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0">
            <form id="confirmReturnForm" action="{{ route('admin.sales.returns.confirm', $return) }}" method="POST" novalidate>
                @csrf
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Confirm Sales Return
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> Confirming will add items back to warehouse stock and create ledger entries for settlement.
                    </div>

                    {{-- Settlement Section --}}
                    <h6 class="mb-3 fw-bold">Settlement Details</h6>

                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Return Amount</small>
                                    <h4 class="text-primary">Rs. {{ number_format($return->total_amount, 2) }}</h4>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Settlement Status</small>
                                    <div class="mt-2">
                                        <span class="badge bg-info">Pending Settlement</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Refund and Credit Amounts --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="refund_amount" class="form-label fw-bold">
                                    Expected Refund Amount
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" class="form-control" name="refund_amount" id="refund_amount"
                                           min="0" max="{{ $return->total_amount }}" step="0.01" value="0"
                                           placeholder="0.00" required>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <strong>Original Return Amount:</strong> Rs. {{ number_format($return->total_amount, 2) }}
                                </small>
                                <small class="text-muted d-block">
                                    <strong>Remaining for Credit:</strong> Rs. <span id="remainingCredit">{{ number_format($return->total_amount, 2) }}</span>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="credit_amount" class="form-label fw-bold">
                                    Customer Credit Amount
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" class="form-control" name="credit_amount" id="credit_amount"
                                           min="0" max="{{ $return->total_amount }}" step="0.01" 
                                           value="{{ $return->total_amount }}" readonly>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <strong>Original Return Amount:</strong> Rs. {{ number_format($return->total_amount, 2) }}
                                </small>
                                <small class="text-muted d-block">
                                    <strong>Refund Entered:</strong> Rs. <span id="refundEntered">0.00</span>
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Validation Messages --}}
                    <div id="validationMessage" class="alert alert-danger d-none" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span id="validationText"></span>
                    </div>

                    {{-- Settlement Summary --}}
                    <div class="card border-2 border-primary mb-4">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Settlement Summary</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td>Return Amount:</td>
                                    <td class="text-end fw-bold">Rs. {{ number_format($return->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Expected Refund:</td>
                                    <td class="text-end">Rs. <span id="summaryRefund">0.00</span></td>
                                </tr>
                                <tr>
                                    <td>Customer Credit:</td>
                                    <td class="text-end">Rs. <span id="summaryCredit">{{ number_format($return->total_amount, 2) }}</span></td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>Settlement Total:</strong></td>
                                    <td class="text-end"><strong>Rs. <span id="summaryTotal">{{ number_format($return->total_amount, 2) }}</span></strong></td>
                                </tr>
                                <tr>
                                    <td colspan="2"><small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Remaining: Rs. <span id="remainingAmount">0.00</span>
                                    </small></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Refund Method --}}
                    <h6 class="mb-3 fw-bold">Refund Information</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="refund_method" class="form-label">Refund Method</label>
                                <select class="form-select" name="refund_method" id="refund_method">
                                    <option value="">-- Select Method --</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="easypaisa">EasyPaisa</option>
                                    <option value="jazz_cash">JazzCash</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="refund_reference" class="form-label">Reference</label>
                                <input type="text" class="form-control" name="refund_reference" id="refund_reference"
                                       placeholder="Transaction ID, Cheque #, etc.">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="settlement_notes" class="form-label">Settlement Notes</label>
                        <textarea class="form-control" name="settlement_notes" id="settlement_notes" rows="2" placeholder="Additional notes about this settlement"></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="confirmReturnForm" class="btn btn-success" id="confirmBtn">
                        <i class="bi bi-check-circle me-1"></i> Confirm Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const returnAmount = {{ $return->total_amount }};
    const refundInput = document.getElementById('refund_amount');
    const creditInput = document.getElementById('credit_amount');
    const validationDiv = document.getElementById('validationMessage');
    const validationText = document.getElementById('validationText');
    const confirmBtn = document.getElementById('confirmBtn');

    function updateCalculations() {
        const refund = parseFloat(refundInput.value) || 0;
        const credit = Math.max(0, returnAmount - refund);

        // Update displayed values
        creditInput.value = credit.toFixed(2);
        document.getElementById('refundEntered').textContent = refund.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('remainingCredit').textContent = credit.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        // Update summary
        document.getElementById('summaryRefund').textContent = refund.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('summaryCredit').textContent = credit.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('summaryTotal').textContent = (refund + credit).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('remainingAmount').textContent = Math.max(0, returnAmount - refund - credit).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        // Validation
        validateSettings(refund, credit);
    }

    function validateSettings(refund, credit) {
        validationDiv.classList.add('d-none');
        confirmBtn.disabled = false;

        if (refund < 0) {
            showValidation('Refund amount cannot be negative');
            return;
        }

        if (credit < 0) {
            showValidation('Credit amount cannot be negative');
            return;
        }

        if (refund > returnAmount) {
            showValidation('Refund amount cannot exceed return amount (Rs. ' + returnAmount.toFixed(2) + ')');
            return;
        }

        if (credit > returnAmount) {
            showValidation('Credit amount cannot exceed return amount (Rs. ' + returnAmount.toFixed(2) + ')');
            return;
        }

        const total = refund + credit;
        if (total > returnAmount + 0.01) { // Allow 1 paisa rounding
            showValidation('Refund + Credit cannot exceed return amount');
            return;
        }
    }

    function showValidation(message) {
        validationText.textContent = message;
        validationDiv.classList.remove('d-none');
        confirmBtn.disabled = true;
    }

    // Event listeners
    refundInput.addEventListener('input', updateCalculations);
    refundInput.addEventListener('change', updateCalculations);

    // Initial calculation
    updateCalculations();
});
</script>
@endpush
@endif

{{-- Cancel Modal --}}
@if($return->status === 'confirmed')
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.sales.returns.cancel', $return) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Sales Return</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Cancelling this return will reverse all stock and ledger adjustments.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reason for Cancellation <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reason" rows="3" required 
                                  placeholder="Explain why this return is being cancelled"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Return</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
