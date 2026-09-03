@extends('layouts.admin')

@section('title', 'Return Details - ' . $return->return_number)

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Return Details</h1>
            <p class="text-muted mb-0">{{ $return->return_number }}</p>
        </div>
        <div>
            <a href="{{ route('admin.sale-returns.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Returns
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Main Content --}}
        <div class="col-lg-8">
            {{-- Return Information --}}
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Return Information</h5>
                    <span class="badge bg-{{ $return->status_badge }} fs-6">
                        {{ $return->status_label }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Return Number</label>
                            <div class="fw-semibold">{{ $return->return_number }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Return Date</label>
                            <div class="fw-semibold">{{ $return->return_date->format('d M Y') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Original Sale</label>
                            <div>
                                <a href="{{ route('admin.sales.show', $return->sale_id) }}" class="text-decoration-none">
                                    <i class="bi bi-file-earmark-text me-1"></i>
                                    {{ $return->sale->invoice_number }}
                                </a>
                                <br>
                                <small class="text-muted">{{ $return->sale->sale_date->format('d M Y') }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Warehouse</label>
                            <div class="fw-semibold">
                                <i class="bi bi-building me-1"></i>
                                {{ $return->warehouse->name }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Customer</label>
                            <div>
                                @if($return->customer)
                                    <strong>{{ $return->customer->name }}</strong>
                                    @if($return->customer->phone)
                                    <br><small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $return->customer->phone }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Walk-in Customer</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Family</label>
                            <div>
                                @if($return->family)
                                    <strong>{{ $return->family->name }}</strong>
                                    <br><small class="text-muted">{{ $return->family->family_code }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </div>
                        @if($return->reason)
                        <div class="col-md-12">
                            <label class="text-muted small">Reason for Return</label>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>{{ $return->reason }}
                            </div>
                        </div>
                        @endif
                        @if($return->notes)
                        <div class="col-md-12">
                            <label class="text-muted small">Additional Notes</label>
                            <div class="border rounded p-3 bg-light">
                                {{ $return->notes }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Original Sale Payment Summary --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Original Sale Payment Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <small class="text-muted d-block mb-1">Sale Total</small>
                                <h5 class="mb-0 text-primary">Rs. {{ number_format($paymentInfo['total_amount'], 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <small class="text-muted d-block mb-1">Paid Amount</small>
                                <h5 class="mb-0 text-success">Rs. {{ number_format($paymentInfo['paid_amount'], 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <small class="text-muted d-block mb-1">Outstanding</small>
                                <h5 class="mb-0 text-danger">Rs. {{ number_format($paymentInfo['outstanding'], 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <small class="text-muted d-block mb-1">Status</small>
                                <h5 class="mb-0">
                                    <span class="badge bg-{{ $paymentInfo['payment_status'] === 'Paid' ? 'success' : ($paymentInfo['payment_status'] === 'Unpaid' ? 'danger' : 'warning') }}">
                                        {{ $paymentInfo['payment_status'] }}
                                    </span>
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Return Items --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Returned Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Original Qty</th>
                                    <th class="text-center">Return Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Return Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($return->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->product->name }}</div>
                                        @if($item->product->sku)
                                        <small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ number_format($item->saleItem->quantity, 2) }}
                                    </td>
                                    <td class="text-center">
                                        <strong class="text-primary">{{ number_format($item->quantity, 2) }}</strong>
                                    </td>
                                    <td class="text-end">
                                        Rs. {{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="text-end">
                                        <strong>Rs. {{ number_format($item->total, 2) }}</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Total Return Amount:</th>
                                    <th class="text-end">
                                        <strong class="text-primary fs-5">
                                            Rs. {{ number_format($return->total_return_amount, 2) }}
                                        </strong>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Confirmation Alert --}}
            @if($return->canBeConfirmed())
            <div class="card border-warning">
                <div class="card-body">
                    <div class="alert alert-warning mb-3">
                        <h6 class="alert-heading">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            This return is in draft status
                        </h6>
                        <p class="mb-0">Confirming this return will:</p>
                        <ul class="mb-0 mt-2">
                            <li>Add returned items back to warehouse stock</li>
                            <li>Adjust customer balance (reduce udhar or create credit)</li>
                            <li>This action cannot be undone</li>
                        </ul>
                    </div>
                    
                    <div class="d-flex gap-2">
                        @can('sales.approve')
                        <form action="{{ route('admin.sale-returns.confirm', $return) }}" method="POST" onsubmit="return confirm('Are you sure you want to confirm this return? This action cannot be undone.');">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i> Confirm Return
                            </button>
                        </form>
                        @endcan
                        
                        @can('sales.cancel')
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="bi bi-x-circle me-1"></i> Cancel Return
                        </button>
                        @endcan
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Summary Card --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">Return Summary</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Total Items:</span>
                        <strong>{{ $return->items->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Total Quantity:</span>
                        <strong>{{ number_format($return->total_quantity, 2) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Return Amount:</span>
                        <strong class="text-primary fs-5">Rs. {{ number_format($return->total_return_amount, 2) }}</strong>
                    </div>
                </div>
            </div>

            {{-- Audit Information --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Audit Trail</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Created By</small>
                        <div>
                            <i class="bi bi-person me-1"></i>
                            {{ $return->creator->name }}
                        </div>
                        <small class="text-muted">{{ $return->created_at->format('d M Y, h:i A') }}</small>
                    </div>
                    
                    @if($return->isConfirmed())
                    <div class="mb-3">
                        <small class="text-muted d-block">Confirmed By</small>
                        <div>
                            <i class="bi bi-person-check me-1"></i>
                            {{ $return->confirmer->name }}
                        </div>
                        <small class="text-muted">{{ $return->confirmed_at->format('d M Y, h:i A') }}</small>
                    </div>
                    @endif
                    
                    <div>
                        <small class="text-muted d-block">Status</small>
                        <span class="badge bg-{{ $return->status_badge }}">
                            {{ $return->status_label }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.sales.show', $return->sale_id) }}" class="btn btn-outline-primary">
                            <i class="bi bi-file-earmark-text me-1"></i> View Original Sale
                        </a>
                        
                        @if($return->customer)
                        <a href="{{ route('admin.customers.statement', $return->customer_id) }}" class="btn btn-outline-info">
                            <i class="bi bi-person me-1"></i> View Customer
                        </a>
                        @endif
                        
                        <a href="{{ route('admin.sale-returns.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-list me-1"></i> All Returns
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Cancel Modal --}}
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Return</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.sale-returns.cancel', $return) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Are you sure you want to cancel this return?
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Cancellation Reason (Optional)</label>
                        <textarea class="form-control" 
                                  id="reason" 
                                  name="reason" 
                                  rows="3" 
                                  placeholder="Enter reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i> Cancel Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
