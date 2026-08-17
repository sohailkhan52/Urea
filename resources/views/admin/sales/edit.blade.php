@extends('layouts.admin')

@section('title', 'Edit Sale - ' . $sale->invoice_number)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Edit Sale: {{ $sale->invoice_number }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.sales.index') }}">Sales</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.sales.show', $sale) }}">{{ $sale->invoice_number }}</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    @if(!$sale->isDraft())
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <strong>Cannot edit confirmed or cancelled sales.</strong> Only draft sales can be edited.
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            {{-- Sale Header --}}
            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('admin.sales.update', $sale) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            {{-- Customer --}}
                            <div class="col-md-6">
                                <label for="customer_id" class="form-label">Customer (Optional)</label>
                                <select class="form-select @error('customer_id') is-invalid @enderror" 
                                        id="customer_id" 
                                        name="customer_id"
                                        {{ !$sale->isDraft() ? 'disabled' : '' }}>
                                    <option value="">-- Walk-in Customer --</option>
                                    @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ $sale->customer_id == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} ({{ $customer->customer_type }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Warehouse --}}
                            <div class="col-md-6">
                                <label for="warehouse_id" class="form-label">Warehouse</label>
                                <input type="text" class="form-control" value="{{ $sale->warehouse->name }}" disabled>
                                <small class="text-muted">Cannot change warehouse for existing sale</small>
                            </div>

                            {{-- Sale Date --}}
                            <div class="col-md-6">
                                <label for="sale_date" class="form-label">Sale Date</label>
                                <input type="date" 
                                       class="form-control @error('sale_date') is-invalid @enderror" 
                                       id="sale_date" 
                                       name="sale_date" 
                                       value="{{ $sale->sale_date->format('Y-m-d') }}"
                                       {{ !$sale->isDraft() ? 'disabled' : '' }}>
                                @error('sale_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <div class="form-control-plaintext pt-2">
                                    @if($sale->isDraft())
                                        <span class="badge bg-warning"><i class="bi bi-exclamation-circle me-1"></i> Draft</span>
                                    @elseif($sale->isConfirmed())
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Confirmed</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Cancelled</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="col-md-12">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" 
                                          name="notes" 
                                          rows="2"
                                          maxlength="1000"
                                          {{ !$sale->isDraft() ? 'disabled' : '' }}>{{ $sale->notes }}</textarea>
                                @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @if($sale->isDraft())
                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Update
                            </button>
                            <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                        </div>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Sale Items --}}
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Sale Items</h5>
                        @if($sale->isDraft())
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                            <i class="bi bi-plus-circle me-1"></i> Add Item
                        </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($sale->items()->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end" style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product->name }}</strong><br>
                                        <small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                    </td>
                                    <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->discount, 2) }}</td>
                                    <td class="text-end">
                                        <strong>{{ number_format($item->total, 2) }}</strong>
                                    </td>
                                    <td class="text-end">
                                        @if($sale->isDraft())
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editItemModal{{ $item->id }}" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('admin.sales.remove-item', $item) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" title="Remove" onclick="return confirm('Remove this item?');">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">No items added yet.</p>
                        @if($sale->isDraft())
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                            <i class="bi bi-plus-circle me-1"></i> Add Item
                        </button>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Summary Sidebar --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Sale Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-6">Subtotal:</div>
                        <div class="col-6 text-end">{{ number_format($sale->subtotal, 2) }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">Sale Discount:</div>
                        <div class="col-6 text-end">
                            @if($sale->isDraft())
                            <div class="input-group input-group-sm">
                                <input type="number" id="discount_input" class="form-control" placeholder="0.00" step="0.01" value="{{ $sale->discount }}">
                            </div>
                            @else
                            {{ number_format($sale->discount, 2) }}
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-2 fs-5">
                        <div class="col-6"><strong>Total:</strong></div>
                        <div class="col-6 text-end"><strong>{{ number_format($sale->total_amount, 2) }}</strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">Paid:</div>
                        <div class="col-6 text-end text-success">{{ number_format($sale->paid_amount, 2) }}</div>
                    </div>
                    <div class="row">
                        <div class="col-6">Due:</div>
                        <div class="col-6 text-end text-danger">{{ number_format($sale->due_amount, 2) }}</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">Items:</div>
                        <div class="col-6 text-end">{{ $sale->items()->count() }}</div>
                    </div>
                    <div class="row">
                        <div class="col-6">Total Qty:</div>
                        <div class="col-6 text-end">{{ number_format($sale->items()->sum('quantity'), 2) }}</div>
                    </div>
                </div>
            </div>

            @if($sale->isDraft())
            <div class="card mt-3">
                <div class="card-body">
                    <form action="{{ route('admin.sales.confirm', $sale) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Confirm this sale? Stock will be reduced from warehouse.');">
                            <i class="bi bi-check-circle me-1"></i> Confirm Sale
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Add Item Modal --}}
    @if($sale->isDraft())
    <div class="modal fade" id="addItemModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Item to Sale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.sales.add-item', $sale) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="product_id" class="form-label">Product <span class="text-danger">*</span></label>
                            <select class="form-select" id="product_id" name="product_id" required onchange="checkStockAvailability()">
                                <option value="">-- Select Product --</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->sale_price }}">
                                    {{ $product->name }} ({{ $product->sku }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity" name="quantity" step="0.01" min="0.01" required>
                                <small class="text-muted" id="stock_available">Available: —</small>
                            </div>
                            <div class="col-md-6">
                                <label for="unit_price" class="form-label">Unit Price <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="unit_price" name="unit_price" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="discount" class="form-label">Item Discount</label>
                            <input type="number" class="form-control" id="discount" name="discount" step="0.01" min="0" value="0">
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

    {{-- Edit Item Modals --}}
    @foreach($sale->items as $item)
    <div class="modal fade" id="editItemModal{{ $item->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.sales.update-item', $item) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <input type="text" class="form-control" value="{{ $item->product->name }} ({{ $item->product->sku }})" disabled>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label for="quantity{{ $item->id }}" class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity{{ $item->id }}" name="quantity" step="0.01" min="0.01" value="{{ $item->quantity }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="unit_price{{ $item->id }}" class="form-label">Unit Price <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="unit_price{{ $item->id }}" name="unit_price" step="0.01" min="0" value="{{ $item->unit_price }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="discount{{ $item->id }}" class="form-label">Item Discount</label>
                            <input type="number" class="form-control" id="discount{{ $item->id }}" name="discount" step="0.01" min="0" value="{{ $item->discount }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
    @endif
</div>

@push('scripts')
<script>
function checkStockAvailability() {
    const productId = document.getElementById('product_id').value;
    if (productId) {
        const option = document.querySelector(`#product_id option[value="${productId}"]`);
        const unitPrice = option.dataset.price;
        document.getElementById('unit_price').value = unitPrice;
        
        // AJAX call to check stock
        fetch(`{{ route('admin.sales.check-stock') }}?product_id=${productId}&warehouse_id={{ $sale->warehouse_id }}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('stock_available').textContent = `Available: ${data.available}`;
            });
    }
}
</script>
@endpush
@endsection
