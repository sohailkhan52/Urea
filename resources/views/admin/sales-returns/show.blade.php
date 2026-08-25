@extends('layouts.admin')

@section('title', 'Sales Return Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Sales Return: {{ $return->return_number }}</h1>
        <a href="{{ route('admin.sales.returns.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Returns
        </a>
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

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

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
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.sales.returns.confirm', $return) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Sales Return</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Confirming this return will:</p>
                    <ul>
                        <li>Add returned items back to warehouse stock</li>
                        <li>Create customer ledger entries</li>
                        <li>Adjust customer balance</li>
                    </ul>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label class="form-label">Refund Amount</label>
                        <input type="number" class="form-control" name="refund_amount" 
                               min="0" max="{{ $return->total_amount }}" step="0.01" value="0">
                        <small class="text-muted">Cash refund to customer</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Credit Amount</label>
                        <input type="number" class="form-control" name="credit_amount" 
                               min="0" max="{{ $return->total_amount }}" step="0.01" value="0">
                        <small class="text-muted">Credit for future purchases</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Refund Method</label>
                        <select class="form-select" name="refund_method">
                            <option value="">-- Select Method --</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="easypaisa">Easypaisa</option>
                            <option value="jazz_cash">JazzCash</option>
                            <option value="cheque">Cheque</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Refund Reference</label>
                        <input type="text" class="form-control" name="refund_reference" 
                               placeholder="Transaction ID, Cheque #, etc.">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Return</button>
                </div>
            </form>
        </div>
    </div>
</div>
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
