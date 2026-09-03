@extends('layouts.admin')

@section('title', 'View Sale - ' . $sale->invoice_number)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Sale Invoice Details</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.sales.index') }}">Sales</a></li>
                        <li class="breadcrumb-item active">{{ $sale->invoice_number }}</li>
                    </ol>
                </nav>
            </div>
            <div class="btn-group">
                @can('sales.view')
                <a href="{{ route('admin.sales.print-invoice', $sale) }}" 
                   class="btn btn-primary" 
                   target="_blank"
                   title="Print Invoice">
                    <i class="bi bi-printer me-1"></i> Print
                </a>
                @endcan
                @if($sale->isDraft())
                    @can('sales.update')
                    <a href="{{ route('admin.sales.edit', $sale) }}" class="btn btn-warning">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    @endcan
                @endif
                <a href="{{ route('admin.sales.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-list me-1"></i> All Sales
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Sale Header --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="bi bi-receipt me-2"></i>
                                {{ $sale->invoice_number }}
                            </h5>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-{{ $sale->status_badge }}">{{ $sale->status_label }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Customer</small>
                            @if($sale->customer)
                                <p class="mb-0">
                                    <strong>{{ $sale->customer->name }}</strong>
                                </p>
                                @if($sale->customer->phone)
                                <small>
                                    <i class="bi bi-telephone me-1"></i>
                                    {{ $sale->customer->phone }}
                                </small>
                                @endif
                                @if($sale->customer->email)
                                <br><small>
                                    <i class="bi bi-envelope me-1"></i>
                                    {{ $sale->customer->email }}
                                </small>
                                @endif
                            @elseif($sale->walkin_customer_name)
                                <p class="mb-0">
                                    <strong>{{ $sale->walkin_customer_name }}</strong>
                                    <span class="badge bg-secondary ms-2">Walk-in</span>
                                </p>
                                @if($sale->walkin_customer_contact)
                                <small>
                                    <i class="bi bi-telephone me-1"></i>
                                    {{ $sale->walkin_customer_contact }}
                                </small>
                                @endif
                            @else
                                <p class="mb-0">
                                    <span class="badge bg-secondary">Walk-in Customer</span>
                                </p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Warehouse</small>
                            <p class="mb-0">
                                <strong>
                                    <i class="bi bi-building me-1"></i>
                                    {{ $sale->warehouse->name }}
                                </strong>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Sale Date</small>
                            <p class="mb-0">
                                <strong>{{ $sale->sale_date->format('M d, Y') }}</strong>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Created</small>
                            <p class="mb-0">
                                <strong>{{ $sale->created_at->format('M d, Y h:i A') }}</strong>
                            </p>
                            <small class="text-muted">By: {{ $sale->creator->name }}</small>
                        </div>
                        @if($sale->isConfirmed())
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Confirmed</small>
                            <p class="mb-0">
                                <strong>{{ $sale->confirmed_at->format('M d, Y h:i A') }}</strong>
                            </p>
                            <small class="text-muted">By: {{ $sale->confirmer?->name ?? 'System' }}</small>
                        </div>
                        @endif
                        @if($sale->isCancelled())
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-2">Cancelled</small>
                            <p class="mb-0">
                                <strong>{{ $sale->cancelled_at->format('M d, Y h:i A') }}</strong>
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sale Items --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-cart me-2"></i>
                        Sale Items ({{ $sale->items()->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($sale->items()->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Return Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->items as $item)
                                @php
                                    $returnedQty = $item->total_returned_quantity ?? 0;
                                    $remaining = $item->returnable_quantity ?? $item->quantity;
                                    $isFullyReturned = $remaining <= 0;
                                    $isPartiallyReturned = $returnedQty > 0 && $remaining > 0;
                                @endphp
                                <tr>
                                    <td>
                                        @if($item->product)
                                            <div class="fw-semibold">{{ $item->product->name }}</div>
                                            <small class="text-muted">
                                                Unit: {{ $item->product->unit }}
                                                @if($item->product->sku)
                                                    | SKU: {{ $item->product->sku }}
                                                @endif
                                            </small>
                                        @else
                                            <div class="fw-semibold text-danger">Product Deleted</div>
                                            <small class="text-muted">Product ID: {{ $item->product_id }}</small>
                                        @endif
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
                                    <td class="text-center">
                                        @if($isFullyReturned)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i> Fully Returned
                                            </span>
                                            <br>
                                            <small class="text-muted">{{ number_format($returnedQty, 2) }} / {{ number_format($item->quantity, 2) }}</small>
                                        @elseif($isPartiallyReturned)
                                            <span class="badge bg-warning">
                                                <i class="bi bi-exclamation-circle me-1"></i> Partially Returned
                                            </span>
                                            <br>
                                            <small class="text-muted">{{ number_format($returnedQty, 2) }} / {{ number_format($item->quantity, 2) }}</small>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-dash-circle me-1"></i> Not Returned
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-bag" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">No items in this sale.</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Notes --}}
            @if($sale->notes)
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-file-text me-2"></i>
                        Notes
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $sale->notes }}</p>
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
                            <strong>{{ number_format($sale->subtotal, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Discount</small>
                            <small class="text-danger">- {{ number_format($sale->discount, 2) }}</small>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-0">Total Amount</h6>
                            <h5 class="mb-0 text-primary">{{ number_format($sale->total_amount, 2) }}</h5>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Paid Amount</small>
                            <strong>{{ number_format($sale->paid_amount, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Balance Due</small>
                            <strong class="text-warning">{{ number_format($sale->due_amount, 2) }}</strong>
                        </div>
                    </div>

                    <div class="alert alert-{{ $sale->paid_amount == 0 ? 'warning' : ($sale->paid_amount >= $sale->total_amount ? 'success' : 'info') }}">
                        <small>
                            <strong>Payment Status:</strong><br>
                            @if($sale->paid_amount == 0)
                                Unpaid
                            @elseif($sale->paid_amount >= $sale->total_amount)
                                Fully Paid
                            @else
                                Partial Payment
                            @endif
                        </small>
                    </div>
                </div>
            </div>

            {{-- Status Info --}}
            @if($sale->isDraft())
            <div class="alert alert-warning mt-3">
                <strong>
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Draft Status
                </strong>
                <p class="mb-0 small mt-2">This sale has not been confirmed yet. No stock has been deducted from the warehouse.</p>
                @can('sales.update')
                <a href="{{ route('admin.sales.edit', $sale) }}" class="btn btn-sm btn-warning mt-2">
                    <i class="bi bi-pencil me-1"></i> Continue Editing
                </a>
                @endcan
            </div>
            @elseif($sale->isConfirmed())
            <div class="alert alert-success mt-3">
                <strong>
                    <i class="bi bi-check-circle me-1"></i>
                    Confirmed
                </strong>
                <p class="mb-0 small mt-2">This sale has been confirmed. Stock has been deducted from the warehouse.</p>
            </div>
            @else
            <div class="alert alert-danger mt-3">
                <strong>
                    <i class="bi bi-x-circle me-1"></i>
                    Cancelled
                </strong>
                <p class="mb-0 small mt-2">This sale has been cancelled. No stock movements were created.</p>
            </div>
            @endif

            {{-- Actions --}}
            @if($sale->isDraft() || $sale->isConfirmed())
            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    @if($sale->isDraft() && $sale->canBeConfirmed())
                        @can('sales.approve')
                        <form action="{{ route('admin.sales.confirm', $sale) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100"
                                    onclick="return confirm('Confirm this sale? Stock will be deducted from the warehouse.');">
                                <i class="bi bi-check-circle me-1"></i> Confirm Sale
                            </button>
                        </form>
                        @endcan
                    @endif

                    @if($sale->isConfirmed())
                        @can('sales.create')
                        <a href="{{ route('admin.sale-returns.create', ['sale_id' => $sale->id]) }}" class="btn btn-danger">
                            <i class="bi bi-arrow-return-left me-1"></i> Create Return
                        </a>
                        @endcan
                    @endif

                    @if($sale->canBeCancelled())
                        @can('sales.cancel')
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="bi bi-x-circle me-1"></i> Cancel Sale
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
                            <strong>{{ $sale->items()->count() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Total Quantity:</span>
                            <strong>{{ $sale->items()->sum('quantity') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cancel Modal --}}
    @can('sales.cancel')
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Sale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.sales.cancel', $sale) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to cancel this sale?</p>
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
                                Cancelled sales will NOT affect warehouse stock.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Cancel Sale</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
</div>
@endsection
