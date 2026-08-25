@extends('layouts.admin')

@section('title', 'View Stock Request - ' . $stockRequest->request_number)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Stock Request Details</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.stock-requests.index') }}">Stock Requests</a></li>
                        <li class="breadcrumb-item active">{{ $stockRequest->request_number }}</li>
                    </ol>
                </nav>
            </div>
            <div class="btn-group">
                @if($stockRequest->canBeEdited())
                    @can('stock_requests.update')
                    <a href="{{ route('admin.stock-requests.edit', $stockRequest) }}" class="btn btn-warning">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    @endcan
                @endif
                <a href="{{ route('admin.stock-requests.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-list me-1"></i> All Requests
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Request Header --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="bi bi-box-seam me-2"></i>
                                {{ $stockRequest->request_number }}
                            </h5>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-{{ $stockRequest->status_badge }} fs-6">{{ $stockRequest->status_label }}</span>
                            <span class="badge bg-{{ $stockRequest->priority_badge }} fs-6 ms-1">{{ $stockRequest->priority_label }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Warehouse</small>
                            <p class="mb-0">
                                <strong>
                                    <i class="bi bi-building me-1"></i>
                                    {{ $stockRequest->warehouse->name }}
                                </strong>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Requested By</small>
                            <p class="mb-0">
                                <strong>{{ $stockRequest->requester->name }}</strong>
                            </p>
                            <small class="text-muted">{{ $stockRequest->requester->email }}</small>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Request Date</small>
                            <p class="mb-0">
                                <strong>{{ $stockRequest->created_at->format('M d, Y h:i A') }}</strong>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Priority</small>
                            <p class="mb-0">
                                <span class="badge bg-{{ $stockRequest->priority_badge }} fs-6">{{ $stockRequest->priority_label }}</span>
                            </p>
                        </div>
                        
                        @if($stockRequest->reason)
                        <div class="col-12">
                            <small class="text-muted d-block mb-2">Reason</small>
                            <p class="mb-0">{{ $stockRequest->reason }}</p>
                        </div>
                        @endif
                        
                        @if($stockRequest->notes)
                        <div class="col-12">
                            <small class="text-muted d-block mb-2">Notes</small>
                            <p class="mb-0">{{ $stockRequest->notes }}</p>
                        </div>
                        @endif

                        {{-- Approval Information --}}
                        @if($stockRequest->isApproved() || $stockRequest->isPartiallyApproved())
                        <div class="col-12">
                            <hr>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Approved By</small>
                            <p class="mb-0">
                                <strong>{{ $stockRequest->approver->name }}</strong>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Approved At</small>
                            <p class="mb-0">
                                <strong>{{ $stockRequest->approved_at->format('M d, Y h:i A') }}</strong>
                            </p>
                        </div>
                        @if($stockRequest->approval_notes)
                        <div class="col-12">
                            <small class="text-muted d-block mb-2">Approval Notes</small>
                            <div class="alert alert-success mb-0">
                                <i class="bi bi-check-circle me-1"></i>
                                {{ $stockRequest->approval_notes }}
                            </div>
                        </div>
                        @endif
                        @endif

                        {{-- Rejection Information --}}
                        @if($stockRequest->isRejected())
                        <div class="col-12">
                            <hr>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Rejected By</small>
                            <p class="mb-0">
                                <strong>{{ $stockRequest->rejecter->name }}</strong>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Rejected At</small>
                            <p class="mb-0">
                                <strong>{{ $stockRequest->rejected_at->format('M d, Y h:i A') }}</strong>
                            </p>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block mb-2">Rejection Reason</small>
                            <div class="alert alert-danger mb-0">
                                <i class="bi bi-x-circle me-1"></i>
                                {{ $stockRequest->rejection_reason }}
                            </div>
                        </div>
                        @endif

                        {{-- Cancellation Information --}}
                        @if($stockRequest->isCancelled())
                        <div class="col-12">
                            <hr>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Cancelled By</small>
                            <p class="mb-0">
                                <strong>{{ $stockRequest->canceller->name }}</strong>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Cancelled At</small>
                            <p class="mb-0">
                                <strong>{{ $stockRequest->cancelled_at->format('M d, Y h:i A') }}</strong>
                            </p>
                        </div>
                        @if($stockRequest->cancellation_reason)
                        <div class="col-12">
                            <small class="text-muted d-block mb-2">Cancellation Reason</small>
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-x-octagon me-1"></i>
                                {{ $stockRequest->cancellation_reason }}
                            </div>
                        </div>
                        @endif
                        @endif

                        {{-- Stock Transfer Link --}}
                        @if($stockRequest->stock_transfer_id)
                        <div class="col-12">
                            <hr>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block mb-2">Linked Stock Transfer</small>
                            <p class="mb-0">
                                @can('transfers.view')
                                <a href="{{ route('admin.stock-transfers.show', $stockRequest->stockTransfer) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-arrow-left-right me-1"></i>
                                    View Transfer: {{ $stockRequest->stockTransfer->transfer_number }}
                                </a>
                                @else
                                <span class="badge bg-info">Transfer: {{ $stockRequest->stockTransfer->transfer_number }}</span>
                                @endcan
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Request Items --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-cart me-2"></i>
                        Request Items ({{ $stockRequest->items()->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($stockRequest->items()->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Requested Qty</th>
                                    @if($stockRequest->isApproved() || $stockRequest->isPartiallyApproved() || $stockRequest->isRejected())
                                    <th class="text-end">Approved Qty</th>
                                    <th class="text-center">Status</th>
                                    @endif
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockRequest->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->product->name }}</div>
                                        <small class="text-muted">
                                            SKU: {{ $item->product->sku }}
                                            @if($item->product->category)
                                            | Category: {{ $item->product->category->name }}
                                            @endif
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ number_format($item->requested_quantity, 2) }}</strong>
                                        <small class="text-muted d-block">{{ $item->product->unit }}</small>
                                    </td>
                                    @if($stockRequest->isApproved() || $stockRequest->isPartiallyApproved() || $stockRequest->isRejected())
                                    <td class="text-end">
                                        @if($item->approved_quantity > 0)
                                            <strong class="text-success">{{ number_format($item->approved_quantity, 2) }}</strong>
                                            <small class="text-muted d-block">{{ $item->product->unit }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item->isFullyApproved())
                                            <span class="badge bg-success">Fully Approved</span>
                                        @elseif($item->isPartiallyApproved())
                                            <span class="badge bg-warning">Partial ({{ round($item->approval_percentage) }}%)</span>
                                        @else
                                            <span class="badge bg-secondary">Not Approved</span>
                                        @endif
                                    </td>
                                    @endif
                                    <td>
                                        <small>{{ $item->notes ?? '—' }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            @if($stockRequest->isApproved() || $stockRequest->isPartiallyApproved())
                            <tfoot class="table-light">
                                <tr>
                                    <td><strong>Totals</strong></td>
                                    <td class="text-end">
                                        <strong>{{ number_format($summary['total_requested_quantity'], 2) }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">{{ number_format($summary['total_approved_quantity'], 2) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ round($summary['approval_percentage']) }}%</span>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3">No items in this request yet.</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Approval Section (Super Admin Only) --}}
            @can('stock_requests.approve')
            @if($stockRequest->canBeApproved())
            <div class="card mb-4" id="approve-section">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-check-circle me-2"></i>
                        Approve Stock Request
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.stock-requests.approve', $stockRequest) }}" method="POST">
                        @csrf
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Instructions:</strong> Review each item and set the approved quantity. You can approve full or partial quantities based on availability.
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-end">Requested</th>
                                        <th class="text-end" style="width: 200px;">Approved Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stockRequest->items as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->product->name }}</strong><br>
                                            <small class="text-muted">{{ $item->product->unit }}</small>
                                        </td>
                                        <td class="text-end">
                                            <strong>{{ number_format($item->requested_quantity, 2) }}</strong>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control" 
                                                   name="approved_quantities[{{ $item->id }}]" 
                                                   value="{{ $item->requested_quantity }}"
                                                   step="0.01"
                                                   min="0"
                                                   max="{{ $item->requested_quantity }}"
                                                   required>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-3">
                            <label for="approval_notes" class="form-label">Approval Notes</label>
                            <textarea class="form-control" 
                                      id="approval_notes" 
                                      name="approval_notes" 
                                      rows="3"
                                      placeholder="Add any notes about this approval..."></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-success" onclick="return confirm('Approve this stock request?');">
                                <i class="bi bi-check-circle me-1"></i> Approve Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
            @endcan
        </div>

        {{-- Right Sidebar --}}
        <div class="col-lg-4">
            {{-- Summary Card --}}
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title">Request Summary</h6>
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="display-6 text-primary">{{ $summary['total_items'] }}</div>
                            <small class="text-muted">Total Items</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="display-6 text-info">{{ number_format($summary['total_requested_quantity'], 2) }}</div>
                            <small class="text-muted">Total Qty</small>
                        </div>
                        @if($summary['total_approved_quantity'] > 0)
                        <div class="col-6">
                            <div class="display-6 text-success">{{ number_format($summary['total_approved_quantity'], 2) }}</div>
                            <small class="text-muted">Approved Qty</small>
                        </div>
                        <div class="col-6">
                            <div class="display-6 text-warning">{{ round($summary['approval_percentage']) }}%</div>
                            <small class="text-muted">Approved</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Actions Card --}}
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title">Actions</h6>
                    <div class="d-grid gap-2">
                        @if($stockRequest->isPending() && $stockRequest->items()->count() > 0)
                            @can('stock_requests.create')
                            <form action="{{ route('admin.stock-requests.submit', $stockRequest) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="btn btn-info w-100"
                                        onclick="return confirm('Submit this request for review?');">
                                    <i class="bi bi-send me-1"></i> Submit for Review
                                </button>
                            </form>
                            @endcan
                        @endif

                        @if($stockRequest->canBeApproved())
                            @can('stock_requests.approve')
                            <a href="#approve-section" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i> Approve Request
                            </a>
                            <button type="button" 
                                    class="btn btn-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#rejectModal">
                                <i class="bi bi-x-circle me-1"></i> Reject Request
                            </button>
                            @endcan
                        @endif

                        @if($stockRequest->canBeCancelled())
                            @can('stock_requests.cancel')
                            <button type="button" 
                                    class="btn btn-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#cancelModal">
                                <i class="bi bi-x-octagon me-1"></i> Cancel Request
                            </button>
                            @endcan
                        @endif

                        @if($stockRequest->canBeEdited())
                            @can('stock_requests.update')
                            <a href="{{ route('admin.stock-requests.edit', $stockRequest) }}" class="btn btn-warning">
                                <i class="bi bi-pencil me-1"></i> Edit Request
                            </a>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>

            {{-- Status Timeline Card --}}
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Status Timeline</h6>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <div class="small text-muted">{{ $stockRequest->created_at->format('M d, Y h:i A') }}</div>
                                <div><strong>Request Created</strong></div>
                                <div class="small">By: {{ $stockRequest->requester->name }}</div>
                            </div>
                        </div>

                        @if($stockRequest->isUnderReview() || $stockRequest->isApproved() || $stockRequest->isPartiallyApproved() || $stockRequest->isRejected())
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <div class="small text-muted">{{ $stockRequest->updated_at->format('M d, Y h:i A') }}</div>
                                <div><strong>Under Review</strong></div>
                            </div>
                        </div>
                        @endif

                        @if($stockRequest->isApproved() || $stockRequest->isPartiallyApproved())
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <div class="small text-muted">{{ $stockRequest->approved_at->format('M d, Y h:i A') }}</div>
                                <div><strong>{{ $stockRequest->isPartiallyApproved() ? 'Partially Approved' : 'Approved' }}</strong></div>
                                <div class="small">By: {{ $stockRequest->approver->name }}</div>
                            </div>
                        </div>
                        @endif

                        @if($stockRequest->isRejected())
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <div class="small text-muted">{{ $stockRequest->rejected_at->format('M d, Y h:i A') }}</div>
                                <div><strong>Rejected</strong></div>
                                <div class="small">By: {{ $stockRequest->rejecter->name }}</div>
                            </div>
                        </div>
                        @endif

                        @if($stockRequest->isCancelled())
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <div class="small text-muted">{{ $stockRequest->cancelled_at->format('M d, Y h:i A') }}</div>
                                <div><strong>Cancelled</strong></div>
                                <div class="small">By: {{ $stockRequest->canceller->name }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
@can('stock_requests.approve')
@if($stockRequest->canBeRejected())
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Stock Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.stock-requests.reject', $stockRequest) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to reject this stock request?</p>
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Request: {{ $stockRequest->request_number }}<br>
                        Warehouse: {{ $stockRequest->warehouse->name }}<br>
                        Requested by: {{ $stockRequest->requester->name }}
                    </div>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">
                            Rejection Reason <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" 
                                  id="rejection_reason" 
                                  name="rejection_reason" 
                                  rows="4"
                                  required
                                  placeholder="Explain why this request is being rejected..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endcan

{{-- Cancel Modal --}}
@can('stock_requests.cancel')
@if($stockRequest->canBeCancelled())
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Stock Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.stock-requests.cancel', $stockRequest) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to cancel this stock request?</p>
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">Reason (optional)</label>
                        <textarea class="form-control" 
                                  id="cancellation_reason" 
                                  name="cancellation_reason" 
                                  rows="3"
                                  placeholder="Enter cancellation reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning">Cancel Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endcan
@endsection

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }
    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: -24px;
        top: 15px;
        width: 2px;
        height: calc(100% - 10px);
        background-color: #dee2e6;
    }
    .timeline-marker {
        position: absolute;
        left: -30px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px currentColor;
    }
    .timeline-content {
        padding-left: 5px;
    }
</style>
@endpush
