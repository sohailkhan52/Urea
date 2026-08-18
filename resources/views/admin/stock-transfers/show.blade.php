@extends('layouts.admin')

@section('title', 'Stock Transfer - ' . $stockTransfer->transfer_number)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Stock Transfer Details</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.stock-transfers.index') }}">Stock Transfers</a></li>
                        <li class="breadcrumb-item active">{{ $stockTransfer->transfer_number }}</li>
                    </ol>
                </nav>
            </div>
            <div class="btn-group">
                @if($stockTransfer->isDraft())
                    @can('stock-transfers.update')
                    <a href="{{ route('admin.stock-transfers.edit', $stockTransfer) }}" class="btn btn-warning">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    @endcan
                @endif
                <a href="{{ route('admin.stock-transfers.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-list me-1"></i> All Transfers
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Transfer Header --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                {{ $stockTransfer->transfer_number }}
                            </h5>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-{{ $stockTransfer->status_badge }}">{{ $stockTransfer->status_label }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">From Warehouse</small>
                            <p class="mb-0">
                                <strong>
                                    <i class="bi bi-building me-1"></i>
                                    {{ $stockTransfer->sourceWarehouse->name }}
                                </strong>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">To Warehouse</small>
                            <p class="mb-0">
                                <strong>
                                    <i class="bi bi-building me-1"></i>
                                    {{ $stockTransfer->destinationWarehouse->name }}
                                </strong>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Transfer Date</small>
                            <p class="mb-0">
                                <strong>{{ $stockTransfer->transfer_date->format('M d, Y') }}</strong>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Created</small>
                            <p class="mb-0">
                                <strong>{{ $stockTransfer->created_at->format('M d, Y h:i A') }}</strong>
                            </p>
                            <small class="text-muted">By: {{ $stockTransfer->creator->name }}</small>
                        </div>

                        @if($stockTransfer->isApproved())
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Approved</small>
                            <p class="mb-0">
                                <strong>{{ $stockTransfer->approved_at->format('M d, Y h:i A') }}</strong>
                            </p>
                            <small class="text-muted">By: {{ $stockTransfer->approver?->name ?? 'System' }}</small>
                        </div>
                        @endif

                        @if($stockTransfer->isDispatched())
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Dispatched</small>
                            <p class="mb-0">
                                <strong>{{ $stockTransfer->dispatched_at->format('M d, Y h:i A') }}</strong>
                            </p>
                            <small class="text-muted">By: {{ $stockTransfer->dispatcher?->name ?? 'System' }}</small>
                        </div>
                        @endif

                        @if($stockTransfer->isReceived())
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Received</small>
                            <p class="mb-0">
                                <strong>{{ $stockTransfer->received_at->format('M d, Y h:i A') }}</strong>
                            </p>
                            <small class="text-muted">By: {{ $stockTransfer->receiver?->name ?? 'System' }}</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Transfer Items --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-cart me-2"></i>
                        Transfer Items ({{ $stockTransfer->items()->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($stockTransfer->items()->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Unit Cost</th>
                                    <th class="text-end">Total Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockTransfer->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->product->name }}</div>
                                        <small class="text-muted">
                                            SKU: {{ $item->product->sku }} | Category: {{ $item->product->category->name }}
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-light text-dark">{{ $item->quantity }}</span>
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($item->unit_cost ?? 0, 2) }}
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ number_format(($item->unit_cost ?? 0) * $item->quantity, 2) }}</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="2" class="text-end"><strong>Total:</strong></td>
                                    <td colspan="2" class="text-end">
                                        <strong>{{ number_format($summary['total_value'] ?? 0, 2) }}</strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-bag" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">No items in this transfer.</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Notes --}}
            @if($stockTransfer->notes)
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-file-text me-2"></i>
                        Notes
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $stockTransfer->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Summary Sidebar --}}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Total Items</small>
                            <strong>{{ $stockTransfer->items()->count() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Total Quantity</small>
                            <strong>{{ $stockTransfer->items()->sum('quantity') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Total Value</small>
                            <strong>{{ number_format($summary['total_value'] ?? 0, 2) }}</strong>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Status</small>
                            <span class="badge bg-{{ $stockTransfer->status_badge }}">{{ $stockTransfer->status_label }}</span>
                        </div>
                    </div>

                    {{-- Status Info --}}
                    @if($stockTransfer->isDraft())
                    <div class="alert alert-warning">
                        <strong>
                            <i class="bi bi-exclamation-circle me-1"></i>
                            Draft
                        </strong>
                        <p class="mb-0 small mt-2">This transfer is in draft status. You can edit or delete it.</p>
                    </div>
                    @elseif($stockTransfer->isPendingApproval())
                    <div class="alert alert-info">
                        <strong>
                            <i class="bi bi-clock me-1"></i>
                            Pending Approval
                        </strong>
                        <p class="mb-0 small mt-2">This transfer is waiting for approval. It cannot be edited.</p>
                    </div>
                    @elseif($stockTransfer->isApproved())
                    <div class="alert alert-info">
                        <strong>
                            <i class="bi bi-check-circle me-1"></i>
                            Approved
                        </strong>
                        <p class="mb-0 small mt-2">This transfer has been approved and can be dispatched.</p>
                    </div>
                    @elseif($stockTransfer->isDispatched())
                    <div class="alert alert-info">
                        <strong>
                            <i class="bi bi-arrow-right me-1"></i>
                            Dispatched
                        </strong>
                        <p class="mb-0 small mt-2">This transfer is in transit. Awaiting confirmation at destination.</p>
                    </div>
                    @elseif($stockTransfer->isInTransit())
                    <div class="alert alert-info">
                        <strong>
                            <i class="bi bi-truck me-1"></i>
                            In Transit
                        </strong>
                        <p class="mb-0 small mt-2">This transfer is currently in transit between warehouses.</p>
                    </div>
                    @elseif($stockTransfer->isReceived())
                    <div class="alert alert-success">
                        <strong>
                            <i class="bi bi-check-lg me-1"></i>
                            Received
                        </strong>
                        <p class="mb-0 small mt-2">This transfer has been completed. Stock has been received.</p>
                    </div>
                    @elseif($stockTransfer->isCancelled())
                    <div class="alert alert-danger">
                        <strong>
                            <i class="bi bi-x-circle me-1"></i>
                            Cancelled
                        </strong>
                        <p class="mb-0 small mt-2">This transfer has been cancelled.</p>
                    </div>
                    @endif

                    {{-- Actions --}}
                    @if($stockTransfer->isDraft() || $stockTransfer->isPendingApproval() || $stockTransfer->isApproved() || $stockTransfer->isDispatched() || $stockTransfer->isInTransit())
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Actions</h5>
                        </div>
                        <div class="card-body d-flex flex-column gap-2">
                            @if($stockTransfer->isDraft() && $stockTransfer->items()->count() > 0)
                                @can('stock-transfers.approve')
                                <form action="{{ route('admin.stock-transfers.submit', $stockTransfer) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100"
                                            onclick="return confirm('Submit this transfer for approval?');">
                                        <i class="bi bi-send me-1"></i> Submit for Approval
                                    </button>
                                </form>
                                @endcan
                            @endif

                            @if($stockTransfer->isPendingApproval())
                                @can('stock-transfers.approve')
                                <form action="{{ route('admin.stock-transfers.approve', $stockTransfer) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100"
                                            onclick="return confirm('Approve this transfer?');">
                                        <i class="bi bi-check-circle me-1"></i> Approve
                                    </button>
                                </form>
                                @endcan
                            @endif

                            @if($stockTransfer->isApproved())
                                @can('stock-transfers.dispatch')
                                <form action="{{ route('admin.stock-transfers.dispatch', $stockTransfer) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-info w-100"
                                            onclick="return confirm('Dispatch this transfer?');">
                                        <i class="bi bi-arrow-right me-1"></i> Dispatch
                                    </button>
                                </form>
                                @endcan
                            @endif

                            @if($stockTransfer->isDispatched())
                                @can('stock-transfers.receive')
                                <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#receiveModal">
                                    <i class="bi bi-check-lg me-1"></i> Confirm Receipt
                                </button>
                                @endcan
                            @endif

                            @if($stockTransfer->isInTransit())
                                @can('stock-transfers.receive')
                                <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#receiveModal">
                                    <i class="bi bi-check-lg me-1"></i> Confirm Receipt
                                </button>
                                @endcan
                            @endif

                            @if($stockTransfer->canBeCancelled())
                                @can('stock-transfers.cancel')
                                <form action="{{ route('admin.stock-transfers.cancel', $stockTransfer) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger w-100"
                                            onclick="return confirm('Cancel this transfer? This action cannot be undone.');">
                                        <i class="bi bi-x-circle me-1"></i> Cancel
                                    </button>
                                </form>
                                @endcan
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Key Info --}}
            <div class="card border-info">
                <div class="card-body">
                    <h6 class="card-title text-info">
                        <i class="bi bi-info-circle me-1"></i>
                        Quick Stats
                    </h6>
                    <div class="small">
                        <div class="d-flex justify-content-between mb-1">
                            <span>From:</span>
                            <strong>{{ $stockTransfer->sourceWarehouse->name }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>To:</span>
                            <strong>{{ $stockTransfer->destinationWarehouse->name }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Items:</span>
                            <strong>{{ $stockTransfer->items()->count() }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receive Transfer Modal -->
<div class="modal fade" id="receiveModal" tabindex="-1" aria-labelledby="receiveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiveModalLabel">
                    <i class="bi bi-check-lg me-2"></i>Confirm Receipt of Transfer Items
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.stock-transfers.receive', $stockTransfer) }}" method="POST" id="receiveForm">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-3">Enter the quantity received for each item. Leave as is to accept the full transfer quantity.</p>
                    
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center" style="width: 120px;">Transferred</th>
                                    <th class="text-center" style="width: 150px;">Received Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockTransfer->items as $item)
                                <tr>
                                    <td>
                                        <small class="fw-semibold">{{ $item->product->name }}</small><br>
                                        <small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">{{ $item->quantity }}</span>
                                    </td>
                                    <td>
                                        <input 
                                            type="number" 
                                            name="received_items[{{ $item->id }}]" 
                                            class="form-control form-control-sm" 
                                            value="{{ $item->quantity }}"
                                            min="1"
                                            max="{{ $item->quantity }}"
                                            step="1"
                                            required
                                        >
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> You can receive partial quantities if needed. Any unreceived items will remain marked as in transit.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i> Confirm Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
