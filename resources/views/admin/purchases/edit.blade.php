@extends('layouts.admin')

@section('title', 'Edit Purchase - ' . $purchase->purchase_number)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Edit Purchase Order</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases</a></li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.purchases.show', $purchase) }}">{{ $purchase->purchase_number }}</a>
                        </li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>
            </div>
            <div class="btn-group">
                <a href="{{ route('admin.purchases.show', $purchase) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to View
                </a>
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
                            <span class="badge bg-warning">Draft</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted">Supplier</small>
                            <p class="mb-0">
                                <strong>{{ $purchase->supplier->name }}</strong>
                                @if($purchase->supplier->company_name)
                                <br><small class="text-muted">{{ $purchase->supplier->company_name }}</small>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Warehouse</small>
                            <p class="mb-0">
                                <strong>
                                    <i class="bi bi-building me-1"></i>
                                    {{ $purchase->warehouse->name }}
                                </strong>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Purchase Date</small>
                            <p class="mb-0">
                                <strong>{{ $purchase->purchase_date->format('M d, Y') }}</strong>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Created</small>
                            <p class="mb-0">
                                <strong>{{ $purchase->created_at->format('M d, Y h:i A') }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Purchase Items --}}
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-cart me-2"></i>
                        Purchase Items ({{ $purchase->items()->count() }})
                    </h5>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Item
                    </button>
                </div>
                <div class="card-body">
                    @if($purchase->items()->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                    <th style="width: 150px;" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->product->name }}</div>
                                        <small class="text-muted">SKU: {{ $item->product->sku }}</small>
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
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" 
                                                    class="btn btn-warning" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editItemModal{{ $item->id }}"
                                                    title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('admin.purchases.removeItem', $item) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Remove this item?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Edit Item Modal --}}
                                <div class="modal fade" id="editItemModal{{ $item->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Item: {{ $item->product->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('admin.purchases.updateItem', $item) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="quantity{{ $item->id }}" class="form-label">Quantity <span class="text-danger">*</span></label>
                                                        <input type="number" 
                                                               class="form-control" 
                                                               id="quantity{{ $item->id }}" 
                                                               name="quantity" 
                                                               value="{{ $item->quantity }}"
                                                               step="0.01"
                                                               min="0.01"
                                                               required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="unit_price{{ $item->id }}" class="form-label">Unit Price <span class="text-danger">*</span></label>
                                                        <input type="number" 
                                                               class="form-control" 
                                                               id="unit_price{{ $item->id }}" 
                                                               name="unit_price" 
                                                               value="{{ $item->unit_price }}"
                                                               step="0.01"
                                                               min="0.01"
                                                               required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update Item</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-bag" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">No items added yet. Click "Add Item" to get started.</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Expenses --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-receipt me-2"></i>
                        Expenses & Costs
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.purchases.updateExpenses', $purchase) }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="discount" class="form-label">Discount</label>
                                <div class="input-group">
                                    <span class="input-group-text">PKR</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="discount" 
                                           name="discount" 
                                           value="{{ $purchase->discount }}"
                                           step="0.01"
                                           min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="transport_cost" class="form-label">Transport Cost</label>
                                <div class="input-group">
                                    <span class="input-group-text">PKR</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="transport_cost" 
                                           name="transport_cost" 
                                           value="{{ $purchase->transport_cost }}"
                                           step="0.01"
                                           min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="other_expenses" class="form-label">Other Expenses</label>
                                <div class="input-group">
                                    <span class="input-group-text">PKR</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="other_expenses" 
                                           name="other_expenses" 
                                           value="{{ $purchase->other_expenses }}"
                                           step="0.01"
                                           min="0">
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-sm btn-secondary">
                                <i class="bi bi-check me-1"></i> Update Expenses
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Notes --}}
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-file-text me-2"></i>
                        Notes
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.purchases.store') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <textarea class="form-control" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3"
                                      placeholder="Add notes or special instructions..."
                                      maxlength="1000">{{ $purchase->notes }}</textarea>
                            <small class="text-muted">Max 1000 characters</small>
                        </div>
                        <button type="submit" class="btn btn-sm btn-secondary">
                            <i class="bi bi-check me-1"></i> Update Notes
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Summary Sidebar --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-calculator me-2"></i>
                        Purchase Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Subtotal</small>
                            <strong>{{ number_format($purchase->subtotal, 2) }}</strong>
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-muted d-block">items: {{ $purchase->items()->count() }}</small>
                            <strong>qty: {{ $purchase->items()->sum('quantity') }}</strong>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Discount</small>
                            <small>- {{ number_format($purchase->discount, 2) }}</small>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Transport</small>
                            <small>+ {{ number_format($purchase->transport_cost, 2) }}</small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Other Expenses</small>
                            <small>+ {{ number_format($purchase->other_expenses, 2) }}</small>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-0">Total Amount</h6>
                            <h5 class="mb-0 text-primary">{{ number_format($purchase->total_amount, 2) }}</h5>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <small>
                            <i class="bi bi-info-circle me-1"></i>
                            Paid: <strong>{{ number_format($purchase->paid_amount, 2) }}</strong>
                        </small>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    @if($purchase->canBeConfirmed())
                    <form action="{{ route('admin.purchases.confirm', $purchase) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100"
                                onclick="return confirm('Confirm this purchase? Stock will be added to the warehouse.');">
                            <i class="bi bi-check-circle me-1"></i> Confirm Purchase
                        </button>
                    </form>
                    @endif

                    @if($purchase->canBeCancelled())
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                        <i class="bi bi-x-circle me-1"></i> Cancel Purchase
                    </button>
                    @endif
                </div>
            </div>

            <div class="card mt-3 border-warning">
                <div class="card-body">
                    <h6 class="card-title text-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i> Draft Status
                    </h6>
                    <p class="mb-0 small text-muted">
                        This purchase is in draft status. You can freely add, edit, or remove items. Stock will only be added to the warehouse after confirmation.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Item Modal --}}
    <div class="modal fade" id="addItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Item to Purchase</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.purchases.addItem', $purchase) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="product_id" class="form-label">Product <span class="text-danger">*</span></label>
                            <select class="form-select" id="product_id" name="product_id" required>
                                <option value="">-- Select Product --</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->name }} (SKU: {{ $product->sku }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control" 
                                   id="quantity" 
                                   name="quantity" 
                                   step="0.01"
                                   min="0.01"
                                   placeholder="Enter quantity"
                                   required>
                        </div>
                        <div class="mb-3">
                            <label for="unit_price" class="form-label">Unit Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">PKR</span>
                                <input type="number" 
                                       class="form-control" 
                                       id="unit_price" 
                                       name="unit_price" 
                                       step="0.01"
                                       min="0.01"
                                       placeholder="Enter unit price"
                                       required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Cancel Modal --}}
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
                        <p>Are you sure you want to cancel this purchase order?</p>
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
</div>
@endsection
