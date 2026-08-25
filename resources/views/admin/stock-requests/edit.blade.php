@extends('layouts.admin')

@section('title', 'Edit Stock Request - ' . $stockRequest->request_number)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Edit Stock Request: {{ $stockRequest->request_number }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.stock-requests.index') }}">Stock Requests</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.stock-requests.show', $stockRequest) }}">{{ $stockRequest->request_number }}</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.stock-requests.show', $stockRequest) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    @if(!$stockRequest->canBeEdited())
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <strong>Cannot edit this request.</strong> Only pending requests can be edited.
    </div>
    @endif

    <div class="row">
        {{-- Left Column: Request Details & Items --}}
        <div class="col-lg-8">
            {{-- REQUEST HEADER SECTION --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Request Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.stock-requests.update', $stockRequest) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            {{-- Warehouse (Display Only) --}}
                            <div class="col-md-6">
                                <label class="form-label">Warehouse</label>
                                <input type="text" 
                                       class="form-control" 
                                       value="{{ $stockRequest->warehouse->name }}" 
                                       disabled>
                                <small class="text-muted">Warehouse cannot be changed</small>
                            </div>

                            {{-- Priority --}}
                            <div class="col-md-6">
                                <label for="priority" class="form-label">
                                    Priority <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('priority') is-invalid @enderror" 
                                        id="priority" 
                                        name="priority" 
                                        required>
                                    @foreach($priorities as $key => $label)
                                    <option value="{{ $key }}" {{ $stockRequest->priority == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Reason --}}
                            <div class="col-12">
                                <label for="reason" class="form-label">
                                    Reason for Request
                                </label>
                                <textarea class="form-control @error('reason') is-invalid @enderror" 
                                          id="reason" 
                                          name="reason" 
                                          rows="2"
                                          placeholder="Brief reason for this stock request...">{{ $stockRequest->reason }}</textarea>
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Notes --}}
                            <div class="col-12">
                                <label for="notes" class="form-label">
                                    Additional Notes
                                </label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" 
                                          name="notes" 
                                          rows="3"
                                          placeholder="Any additional information...">{{ $stockRequest->notes }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Update Request Details
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ITEMS SECTION --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Request Items</h5>
                        <button type="button" 
                                class="btn btn-sm btn-primary" 
                                data-bs-toggle="modal" 
                                data-bs-target="#addItemModal">
                            <i class="bi bi-plus-circle me-1"></i> Add Product
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($stockRequest->items->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end" style="width: 150px;">Requested Qty</th>
                                    <th style="width: 200px;">Notes</th>
                                    <th class="text-end" style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockRequest->items as $item)
                                <tr id="item-row-{{ $item->id }}">
                                    <td>
                                        <div>
                                            <strong>{{ $item->product->name }}</strong>
                                        </div>
                                        <small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ number_format($item->requested_quantity, 2) }}</strong>
                                        <small class="text-muted d-block">{{ $item->product->unit }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $item->notes ?? '—' }}</small>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" 
                                                class="btn btn-sm btn-warning" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editItemModal{{ $item->id }}"
                                                title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('admin.stock-requests.removeItem', $item) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Remove this product from the request?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
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
                                            <form action="{{ route('admin.stock-requests.updateItem', $item) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="quantity{{ $item->id }}" class="form-label">
                                                            Requested Quantity <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="number" 
                                                               class="form-control" 
                                                               id="quantity{{ $item->id }}" 
                                                               name="quantity" 
                                                               value="{{ $item->requested_quantity }}"
                                                               step="0.01"
                                                               min="0.01"
                                                               required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="notes{{ $item->id }}" class="form-label">Notes</label>
                                                        <textarea class="form-control" 
                                                                  id="notes{{ $item->id }}" 
                                                                  name="notes" 
                                                                  rows="2"
                                                                  placeholder="Additional notes for this item...">{{ $item->notes }}</textarea>
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
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3">No items added yet. Click "Add Product" to start adding items to this request.</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ACTIONS --}}
            @if($stockRequest->items->count() > 0)
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Ready to submit?</h6>
                            <p class="small text-muted mb-0">Submit this request for review by Super Admin</p>
                        </div>
                        <form action="{{ route('admin.stock-requests.submit', $stockRequest) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" 
                                    class="btn btn-success"
                                    onclick="return confirm('Submit this request for review? You won\'t be able to edit it after submission.');">
                                <i class="bi bi-send me-1"></i> Submit for Review
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column: Summary & Info --}}
        <div class="col-lg-4">
            {{-- Status Card --}}
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title">Request Status</h6>
                    <div class="mb-3">
                        <span class="badge bg-{{ $stockRequest->status_badge }} fs-6">
                            {{ $stockRequest->status_label }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Request Number:</small><br>
                        <strong>{{ $stockRequest->request_number }}</strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Created:</small><br>
                        <strong>{{ $stockRequest->created_at->format('M d, Y h:i A') }}</strong>
                    </div>
                    <div>
                        <small class="text-muted">Priority:</small><br>
                        <span class="badge bg-{{ $stockRequest->priority_badge }}">{{ $stockRequest->priority_label }}</span>
                    </div>
                </div>
            </div>

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
                            <small class="text-muted">Total Quantity</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Help Card --}}
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-info-circle me-1"></i> Editing Tips
                    </h6>
                    <ul class="small mb-0">
                        <li>Add products one by one using the "Add Product" button</li>
                        <li>You can edit quantities and notes for each item</li>
                        <li>Remove items that are no longer needed</li>
                        <li>Submit for review when ready</li>
                        <li>Once submitted, you cannot edit the request</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Item Modal --}}
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Product to Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.stock-requests.addItem', $stockRequest) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="product_id" class="form-label">
                            Product <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="product_id" name="product_id" required>
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}" data-unit="{{ $product->unit }}">
                                {{ $product->name }} ({{ $product->sku }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">
                            Requested Quantity <span class="text-danger">*</span>
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="quantity" 
                               name="quantity" 
                               step="0.01"
                               min="0.01"
                               required>
                        <small class="text-muted">Unit: <span id="unit-display">—</span></small>
                    </div>
                    <div class="mb-3">
                        <label for="item_notes" class="form-label">Notes</label>
                        <textarea class="form-control" 
                                  id="item_notes" 
                                  name="notes" 
                                  rows="2"
                                  placeholder="Additional notes for this item..."></textarea>
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
@endsection

@push('scripts')
<script>
    // Show unit when product is selected
    document.getElementById('product_id')?.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const unit = selectedOption.dataset.unit || '—';
        document.getElementById('unit-display').textContent = unit;
    });
</script>
@endpush
