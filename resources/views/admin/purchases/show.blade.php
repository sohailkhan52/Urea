@extends('layouts.admin')

@section('title', 'View Purchase - ' . $purchase->purchase_number)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Purchase Order Details</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases</a></li>
                        <li class="breadcrumb-item active">{{ $purchase->purchase_number }}</li>
                    </ol>
                </nav>
            </div>
            <div class="btn-group">
                @if($purchase->isDraft())
                    @can('purchases.update')
                    <a href="{{ route('admin.purchases.edit', $purchase) }}" class="btn btn-warning">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    @endcan
                @endif
                <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-list me-1"></i> All Purchases
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Purchase Header --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="bi bi-file-earmark-text me-2"></i>
                                {{ $purchase->purchase_number }}
                            </h5>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-{{ $purchase->status_badge }}">{{ $purchase->status_label }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Supplier</small>
                            <p class="mb-0">
                                <strong>{{ $purchase->supplier->name }}</strong>
                            </p>
                            @if($purchase->supplier->company_name)
                            <small class="text-muted">{{ $purchase->supplier->company_name }}</small>
                            @endif
                            @if($purchase->supplier->phone)
                            <br><small>
                                <i class="bi bi-telephone me-1"></i>
                                {{ $purchase->supplier->phone }}
                            </small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Warehouse</small>
                            <p class="mb-0">
                                <strong>
                                    <i class="bi bi-building me-1"></i>
                                    {{ $purchase->warehouse->name }}
                                </strong>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Purchase Date</small>
                            <p class="mb-0">
                                <strong>{{ $purchase->purchase_date->format('M d, Y') }}</strong>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Created</small>
                            <p class="mb-0">
                                <strong>{{ $purchase->created_at->format('M d, Y h:i A') }}</strong>
                            </p>
                            <small class="text-muted">By: {{ $purchase->creator->name }}</small>
                        </div>
                        @if($purchase->isConfirmed())
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Confirmed</small>
                            <p class="mb-0">
                                <strong>{{ $purchase->confirmed_at->format('M d, Y h:i A') }}</strong>
                            </p>
                            <small class="text-muted">By: {{ $purchase->confirmer?->name ?? 'System' }}</small>
                        </div>
                        @endif
                        @if($purchase->isCancelled())
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Cancelled</small>
                            <p class="mb-0">
                                <strong>{{ $purchase->cancelled_at->format('M d, Y h:i A') }}</strong>
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Purchase Items --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-cart me-2"></i>
                        Purchase Items ({{ $purchase->items()->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($purchase->items()->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->items as $item)
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
                                        {{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ number_format($item->total, 2) }}</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-bag" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">No items in this purchase.</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Notes --}}
            @if($purchase->notes)
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-file-text me-2"></i>
                        Notes
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $purchase->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Summary Sidebar --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-calculator me-2"></i>
                        Financial Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Items Subtotal</small>
                            <strong>{{ number_format($purchase->subtotal, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Discount</small>
                            <small class="text-danger">- {{ number_format($purchase->discount, 2) }}</small>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Transport Cost</small>
                            <small class="text-success">+ {{ number_format($purchase->transport_cost, 2) }}</small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Other Expenses</small>
                            <small class="text-success">+ {{ number_format($purchase->other_expenses, 2) }}</small>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-0">Total Amount</h6>
                            <h5 class="mb-0 text-primary">{{ number_format($purchase->total_amount, 2) }}</h5>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Paid Amount</small>
                            <strong>{{ number_format($purchase->paid_amount, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Balance Due</small>
                            <strong class="text-warning">{{ number_format($purchase->payable_amount, 2) }}</strong>
                        </div>
                    </div>

                    <div class="alert alert-{{ $purchase->paid_amount == 0 ? 'warning' : ($purchase->paid_amount >= $purchase->total_amount ? 'success' : 'info') }}">
                        <small>
                            <strong>Payment Status:</strong><br>
                            {{ $purchase->payment_status }}
                        </small>
                    </div>
                </div>
            </div>

            {{-- Status Info --}}
            @if($purchase->isDraft())
            <div class="alert alert-warning mt-3">
                <strong>
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Draft Status
                </strong>
                <p class="mb-0 small mt-2">This purchase has not been confirmed yet. No stock has been added to the warehouse.</p>
                @can('purchases.update')
                <a href="{{ route('admin.purchases.edit', $purchase) }}" class="btn btn-sm btn-warning mt-2">
                    <i class="bi bi-pencil me-1"></i> Continue Editing
                </a>
                @endcan
            </div>
            @elseif($purchase->isConfirmed())
            <div class="alert alert-success mt-3">
                <strong>
                    <i class="bi bi-check-circle me-1"></i>
                    Confirmed
                </strong>
                <p class="mb-0 small mt-2">This purchase has been confirmed. Stock has been added to the warehouse.</p>
            </div>
            @else
            <div class="alert alert-danger mt-3">
                <strong>
                    <i class="bi bi-x-circle me-1"></i>
                    Cancelled
                </strong>
                <p class="mb-0 small mt-2">This purchase has been cancelled. No stock movements were created.</p>
            </div>
            @endif

            {{-- Actions --}}
            @if($purchase->isDraft() || $purchase->isConfirmed())
            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    @if($purchase->isDraft() && $purchase->canBeConfirmed())
                        @can('purchases.approve')
                        <form action="{{ route('admin.purchases.confirm', $purchase) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100"
                                    onclick="return confirm('Confirm this purchase? Stock will be added to the warehouse.');">
                                <i class="bi bi-check-circle me-1"></i> Confirm Purchase
                            </button>
                        </form>
                        @endcan
                    @endif

                    @if($purchase->canBeCancelled())
                        @can('purchases.cancel')
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="bi bi-x-circle me-1"></i> Cancel Purchase
                        </button>
                        @endcan
                    @endif
                </div>
            </div>
            @endif

            {{-- Key Info --}}
            <div class="card mt-3 border-info">
                <div class="card-body">
                    <h6 class="card-title text-info">
                        <i class="bi bi-info-circle me-1"></i>
                        Quick Stats
                    </h6>
                    <div class="small">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Total Items:</span>
                            <strong>{{ $purchase->items()->count() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Total Quantity:</span>
                            <strong>{{ $purchase->items()->sum('quantity') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cancel Modal --}}
    @can('purchases.cancel')
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Purchase</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.purchases.cancel', $purchase) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to cancel this purchase?</p>
                        <div class="mb-3">
                            <label for="reason" class="form-label">Cancellation Reason (optional)</label>
                            <textarea class="form-control" 
                                      id="reason" 
                                      name="reason" 
                                      rows="3"
                                      placeholder="Enter cancellation reason..."></textarea>
                        </div>
                        <div class="alert alert-info">
                            <small>
                                <i class="bi bi-info-circle me-1"></i>
                                Cancelled purchases will NOT affect warehouse stock.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Cancel Purchase</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
</div>
@endsection
