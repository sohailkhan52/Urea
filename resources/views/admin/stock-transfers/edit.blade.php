@extends('layouts.admin')

@section('title', 'Edit Stock Transfer')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Edit Stock Transfer</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.stock-transfers.index') }}">Stock Transfers</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.stock-transfers.show', $stockTransfer) }}">Transfer #{{ $stockTransfer->id }}</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.stock-transfers.show', $stockTransfer) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.stock-transfers.update', $stockTransfer) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            {{-- Source Warehouse --}}
                            <div class="col-md-6">
                                <label for="source_warehouse_id" class="form-label">From Warehouse <span class="text-danger">*</span></label>
                                <select class="form-select @error('source_warehouse_id') is-invalid @enderror" 
                                        id="source_warehouse_id" 
                                        name="source_warehouse_id" 
                                        required
                                        {{ $stockTransfer->isApproved() ? 'disabled' : '' }}>
                                    @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" 
                                            {{ old('source_warehouse_id', $stockTransfer->source_warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('source_warehouse_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Destination Warehouse --}}
                            <div class="col-md-6">
                                <label for="destination_warehouse_id" class="form-label">To Warehouse <span class="text-danger">*</span></label>
                                <select class="form-select @error('destination_warehouse_id') is-invalid @enderror" 
                                        id="destination_warehouse_id" 
                                        name="destination_warehouse_id" 
                                        required
                                        {{ $stockTransfer->isApproved() ? 'disabled' : '' }}>
                                    @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" 
                                            {{ old('destination_warehouse_id', $stockTransfer->destination_warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('destination_warehouse_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Transfer Date --}}
                            <div class="col-md-6">
                                <label for="transfer_date" class="form-label">Transfer Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('transfer_date') is-invalid @enderror" 
                                       id="transfer_date" 
                                       name="transfer_date" 
                                       value="{{ old('transfer_date', $stockTransfer->transfer_date->format('Y-m-d')) }}"
                                       min="{{ date('Y-m-d') }}"
                                       required
                                       {{ $stockTransfer->isApproved() ? 'disabled' : '' }}>
                                @error('transfer_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <div class="form-control-plaintext pt-2">
                                    @if($stockTransfer->isDraft())
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-exclamation-circle me-1"></i> Draft
                                        </span>
                                    @elseif($stockTransfer->isPendingApproval())
                                        <span class="badge bg-warning">
                                            <i class="bi bi-clock me-1"></i> Pending Approval
                                        </span>
                                    @elseif($stockTransfer->isApproved())
                                        <span class="badge bg-info">
                                            <i class="bi bi-check-circle me-1"></i> Approved
                                        </span>
                                    @elseif($stockTransfer->isDispatched())
                                        <span class="badge bg-primary">
                                            <i class="bi bi-arrow-right me-1"></i> Dispatched
                                        </span>
                                    @elseif($stockTransfer->isInTransit())
                                        <span class="badge bg-primary">
                                            <i class="bi bi-truck me-1"></i> In Transit
                                        </span>
                                    @elseif($stockTransfer->isReceived())
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-lg me-1"></i> Received
                                        </span>
                                    @elseif($stockTransfer->isCancelled())
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle me-1"></i> Cancelled
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="col-md-12">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" 
                                          name="notes" 
                                          rows="3"
                                          placeholder="Add any additional notes or special instructions..."
                                          maxlength="1000"
                                          {{ $stockTransfer->isApproved() ? 'disabled' : '' }}>{{ old('notes', $stockTransfer->notes) }}</textarea>
                                @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Max 1000 characters</small>
                            </div>
                        </div>

                        {{-- Hidden fields for approved transfers --}}
                        @if($stockTransfer->isApproved())
                            <input type="hidden" name="source_warehouse_id" value="{{ $stockTransfer->source_warehouse_id }}">
                            <input type="hidden" name="destination_warehouse_id" value="{{ $stockTransfer->destination_warehouse_id }}">
                            <input type="hidden" name="transfer_date" value="{{ $stockTransfer->transfer_date->format('Y-m-d') }}">
                        @endif

                        <div class="mt-4 d-flex gap-2">
                            @if($stockTransfer->isDraft())
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i> Save Changes
                                </button>
                            @else
                                <button type="submit" class="btn btn-primary" disabled>
                                    <i class="bi bi-check-circle me-1"></i> Save Changes
                                </button>
                                <small class="text-muted mt-2 d-block">This transfer cannot be edited in its current status.</small>
                            @endif
                            <a href="{{ route('admin.stock-transfers.show', $stockTransfer) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Transfer Items Section --}}
            @if($stockTransfer->isDraft())
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-cart me-2"></i>
                        Add Transfer Items
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.stock-transfers.addItem', $stockTransfer) }}" method="POST" class="row g-3">
                        @csrf

                        {{-- Product Selection --}}
                        <div class="col-md-6">
                            <label for="product_id" class="form-label">Product <span class="text-danger">*</span></label>
                            <select class="form-select @error('product_id') is-invalid @enderror" 
                                    id="product_id" 
                                    name="product_id" 
                                    required
                                    onchange="updateAvailableStock()">
                                <option value="">-- Select Product --</option>
                                @foreach($productsWithStock as $product)
                                <option value="{{ $product['id'] }}" data-available="{{ $product['available_stock'] }}">
                                    {{ $product['name'] }} (SKU: {{ $product['sku'] }}) - Available: {{ $product['available_stock'] }}
                                </option>
                                @endforeach
                            </select>
                            @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if(count($productsWithStock) == 0)
                            <small class="text-warning mt-2 d-block">
                                <i class="bi bi-exclamation-circle me-1"></i>
                                No products available in the source warehouse.
                            </small>
                            @endif
                        </div>

                        {{-- Quantity --}}
                        <div class="col-md-4">
                            <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('quantity') is-invalid @enderror" 
                                   id="quantity" 
                                   name="quantity" 
                                   placeholder="Enter quantity"
                                   min="0.01"
                                   step="0.01"
                                   required>
                            <small id="availableQtyHelper" class="text-muted d-block mt-1"></small>
                            @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Add Button --}}
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100" id="addItemBtn">
                                <i class="bi bi-plus-circle me-1"></i> Add
                            </button>
                        </div>
                    </form>

                    <script>
                    function updateAvailableStock() {
                        const select = document.getElementById('product_id');
                        const selectedOption = select.options[select.selectedIndex];
                        const availableStock = selectedOption.getAttribute('data-available');
                        const availableQtyHelper = document.getElementById('availableQtyHelper');
                        
                        if (availableStock) {
                            availableQtyHelper.textContent = `Available: ${availableStock} units`;
                        } else {
                            availableQtyHelper.textContent = '';
                        }
                    }
                    </script>
                </div>
            </div>

            {{-- Transfer Items List --}}
            @if($stockTransfer->items()->count() > 0)
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check me-2"></i>
                        Items ({{ $stockTransfer->items()->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center" style="width: 100px;">Quantity</th>
                                    <th class="text-center" style="width: 100px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockTransfer->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->product->name }}</div>
                                        <small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $item->quantity }}</span>
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('admin.stock-transfers.removeItem', $item) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Remove this item from transfer?');">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            @endif
        </div>

        <div class="col-lg-4">
            {{-- Transfer Summary --}}
            @if($summary)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Transfer Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-muted">Total Items</small>
                            <div class="h4 mb-0">{{ $summary['items_count'] ?? 0 }}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Total Qty</small>
                            <div class="h4 mb-0">{{ $summary['total_quantity'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="card mt-3 bg-light">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-info-circle me-1"></i> Transfer Guidelines
                    </h5>
                    <ul class="mb-0 small">
                        <li><strong>Edit Available:</strong> Only draft transfers can be edited.</li>
                        <li><strong>From/To Warehouse:</strong> Required. Must be different warehouses.</li>
                        <li><strong>Transfer Date:</strong> Required. Cannot be in the past.</li>
                        <li><strong>Items:</strong> Manage items from the transfer details page.</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-lightbulb me-1"></i> Transfer Process
                    </h6>
                    <ol class="mb-0 small text-muted">
                        <li>Create draft transfer</li>
                        <li>Add products and quantities</li>
                        <li>Submit for approval</li>
                        <li>Approve transfer</li>
                        <li>Dispatch to reduce source stock</li>
                        <li>Mark as in transit</li>
                        <li>Receive items at destination</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
