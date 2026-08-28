@extends('layouts.admin')

@section('title', 'Purchase Return Details')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2">Purchase Return: <strong>{{ $return->return_number }}</strong></h1>
            <p class="text-muted mb-0">PO: <a href="{{ route('admin.purchases.show', $return->purchase) }}">{{ $return->purchase->purchase_number }}</a></p>
        </div>
        <div class="btn-group">
            @if($return->status === 'confirmed')
            <a href="{{ route('admin.purchases.returns.print', $return) }}" class="btn btn-primary" target="_blank">
                <i class="bi bi-printer me-1"></i> Print
            </a>
            @endif
            <a href="{{ route('admin.purchases.returns.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    {{-- Error Messages (keep errors as they're specific to this page) --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>
        <strong>Errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Status Bar --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex gap-4">
                        <div>
                            <small class="text-muted d-block">Status</small>
                            @if($return->status === 'draft')
                                <span class="badge bg-warning fs-6">
                                    <i class="bi bi-pencil-square me-1"></i> Draft
                                </span>
                            @elseif($return->status === 'confirmed')
                                <span class="badge bg-success fs-6">
                                    <i class="bi bi-check-circle me-1"></i> Confirmed
                                </span>
                            @elseif($return->status === 'cancelled')
                                <span class="badge bg-danger fs-6">
                                    <i class="bi bi-x-circle me-1"></i> Cancelled
                                </span>
                            @endif
                        </div>
                        <div>
                            <small class="text-muted d-block">Return Type</small>
                            @if($return->return_type === 'WHOLE_ORDER')
                                <span class="badge bg-info">Whole Order</span>
                            @else
                                <span class="badge bg-secondary">Partial Items</span>
                            @endif
                        </div>
                        <div>
                            <small class="text-muted d-block">Return Date</small>
                            <strong>{{ $return->return_date->format('d M Y') }}</strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    @if($return->status === 'draft' && auth()->user()->can('purchases.approve'))
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmModal">
                        <i class="bi bi-check-circle me-1"></i> Confirm Return
                    </button>
                    @endif
                    @if($return->status !== 'cancelled' && auth()->user()->can('purchases.cancel'))
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="row">
        {{-- Left Column --}}
        <div class="col-lg-8">
            {{-- Return Information Card --}}
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text text-primary me-2"></i>
                        Return Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Return Number</small>
                            <strong class="fs-5">{{ $return->return_number }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Purchase Order</small>
                            <a href="{{ route('admin.purchases.show', $return->purchase) }}" class="text-decoration-none">
                                <strong class="fs-5">{{ $return->purchase->purchase_number }}</strong>
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Supplier</small>
                            <strong>{{ $return->purchase->supplier->name }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Warehouse</small>
                            <strong>{{ $return->purchase->warehouse->name }}</strong>
                        </div>
                        <div class="col-md-6 mb-0">
                            <small class="text-muted d-block">Created By</small>
                            <strong>{{ $return->creator->name }}</strong>
                        </div>
                        <div class="col-md-6 mb-0">
                            <small class="text-muted d-block">Created On</small>
                            <strong>{{ $return->created_at->format('d M Y H:i') }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Returned Items Card --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-box text-primary me-2"></i>
                        Returned Items
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($return->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product->name }}</strong>
                                        @if($item->reason)
                                        <br>
                                        <small class="text-muted">Reason: {{ $item->reason }}</small>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="text-end">Rs. {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">Rs. {{ number_format($item->discount ?? 0, 2) }}</td>
                                    <td class="text-end">
                                        <strong>Rs. {{ number_format(($item->quantity * $item->unit_price) - ($item->discount ?? 0), 2) }}</strong>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No items in this return
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="table-secondary fw-bold">
                                    <td colspan="4" class="text-end">Total Return Amount:</td>
                                    <td class="text-end">Rs. {{ number_format($return->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Additional Information --}}
            @if($return->reason || $return->notes)
            <div class="card mt-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-chat-dots text-primary me-2"></i>
                        Additional Information
                    </h5>
                </div>
                <div class="card-body">
                    @if($return->reason)
                    <div class="mb-3">
                        <small class="text-muted d-block">Reason for Return</small>
                        <p class="mb-0">{{ $return->reason }}</p>
                    </div>
                    @endif
                    @if($return->notes)
                    <div>
                        <small class="text-muted d-block">Notes</small>
                        <p class="mb-0">{{ $return->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column --}}
        <div class="col-lg-4">
            {{-- Financial Summary Card --}}
            <div class="card border-0 shadow-sm mb-4 bg-gradient">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-calculator text-primary me-2"></i>
                        Financial Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Subtotal</small>
                        <strong>Rs. {{ number_format($return->subtotal ?? $return->total_amount, 2) }}</strong>
                    </div>
                    @if(($return->discount_amount ?? 0) > 0)
                    <div class="mb-3">
                        <small class="text-muted d-block">Discount</small>
                        <strong class="text-danger">-Rs. {{ number_format($return->discount_amount, 2) }}</strong>
                    </div>
                    @endif
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block">Return Amount</small>
                        <h4 class="mb-0 text-primary">Rs. {{ number_format($return->total_amount, 2) }}</h4>
                    </div>

                    {{-- Settlement Info (only if confirmed) --}}
                    @if($return->status === 'confirmed')
                    <div class="mb-3">
                        <small class="text-muted d-block">Refund Amount</small>
                        <strong>Rs. {{ number_format($return->refund_amount ?? 0, 2) }}</strong>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Supplier Credit</small>
                        <strong class="text-success">Rs. {{ number_format($return->supplier_credit_amount ?? 0, 2) }}</strong>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Settlement Details (if confirmed) --}}
            @if($return->status === 'confirmed')
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-bank text-primary me-2"></i>
                        Settlement Details
                    </h5>
                </div>
                <div class="card-body">
                    @if($return->refund_method)
                    <div class="mb-3">
                        <small class="text-muted d-block">Refund Method</small>
                        <strong class="text-capitalize">{{ str_replace('_', ' ', $return->refund_method) }}</strong>
                    </div>
                    @endif

                    @if($return->refund_reference)
                    <div class="mb-3">
                        <small class="text-muted d-block">Reference</small>
                        <strong>{{ $return->refund_reference }}</strong>
                    </div>
                    @endif

                    @if($return->settlement_notes)
                    <div>
                        <small class="text-muted d-block">Settlement Notes</small>
                        <p class="mb-0 small">{{ $return->settlement_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Confirm Modal --}}
@if($return->status === 'draft')
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0">
            <form action="{{ route('admin.purchases.returns.confirm', $return) }}" method="POST">
                @csrf
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Confirm Purchase Return
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> Confirming will remove items from warehouse stock and create ledger entries for settlement.
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
                                    Supplier Credit Amount
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
                                    <td>Supplier Credit:</td>
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
                    <button type="submit" class="btn btn-success" id="confirmBtn">
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
@if($return->status !== 'cancelled')
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0">
            <form action="{{ route('admin.purchases.returns.cancel', $return) }}" method="POST">
                @csrf
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title">
                        <i class="bi bi-x-circle text-danger me-2"></i>
                        Cancel Return
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This will reverse all stock and ledger entries associated with this return.
                    </div>
                    <div class="mb-3">
                        <label for="cancel_reason" class="form-label fw-bold">
                            Cancellation Reason <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" name="reason" id="cancel_reason" rows="4" required
                                  placeholder="Please explain why this return is being cancelled..."></textarea>
                        <small class="text-muted">This information will be recorded in the audit trail</small>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Return</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i> Cancel Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
