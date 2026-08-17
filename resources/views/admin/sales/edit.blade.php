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
                    <form action="{{ route('admin.sales.update', $sale) }}" method="POST" id="updateSaleForm">
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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmWithPaymentModal">
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
                                            <form action="{{ route('admin.sales.removeItem', $item) }}" method="POST" class="d-inline">
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
                    <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#confirmWithPaymentModal">
                        <i class="bi bi-check-circle me-1"></i> Confirm Sale
                    </button>
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
                <form action="{{ route('admin.sales.addItem', $sale) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="product_id" class="form-label">Product <span class="text-danger">*</span></label>
                            <select class="form-select" id="product_id" name="product_id" required onchange="checkStockAvailability()">
                                <option value="">-- Select Product --</option>
                                @foreach($products as $product)
                                    @php
                                        $warehouseStock = $stockService->getCurrentStock($sale->warehouse_id, $product->id);
                                        $isDisabled = $warehouseStock <= 0;
                                    @endphp
                                    <option value="{{ $product->id }}" 
                                            data-price="{{ $product->sale_price }}" 
                                            data-stock="{{ $warehouseStock }}"
                                            {{ $isDisabled ? 'disabled' : '' }}>
                                        {{ $product->name }} ({{ $product->sku }}) 
                                        {{ $isDisabled ? '❌ Out of Stock' : '✓ ' . $warehouseStock . ' units' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">
                                <i class="bi bi-info-circle"></i> Out of stock items are disabled
                            </small>
                        </div>

                        <!-- Stock Availability Alert -->
                        <div class="alert alert-info d-none" id="stock_alert" role="alert">
                            <strong>Warehouse Stock:</strong> <span id="stock_available_text">—</span> units available in <strong>{{ $sale->warehouse->name }}</strong>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity" name="quantity" step="0.01" min="0.01" required placeholder="Enter quantity">
                                <small class="text-muted" id="stock_warning"></small>
                            </div>
                            <div class="col-md-6">
                                <label for="unit_price" class="form-label">Unit Price <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="unit_price" name="unit_price" step="0.01" min="0" required placeholder="Auto-filled">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="discount" class="form-label">Item Discount</label>
                            <input type="number" class="form-control" id="discount" name="discount" step="0.01" min="0" value="0" placeholder="0.00">
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
                <form action="{{ route('admin.sales.updateItem', $item) }}" method="POST">
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

{{-- Confirm Sale with Payment Modal --}}
<div class="modal fade" id="confirmWithPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Sale & Record Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.sales.confirm', $sale) }}" method="POST" id="confirmSaleForm">
                @csrf
                <div class="modal-body">
                    {{-- Sale Total --}}
                    <div class="alert alert-light border mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted"><strong>Total Amount:</strong></span>
                            <strong class="text-primary h5 mb-0">Rs. {{ number_format($sale->total_amount, 2) }}</strong>
                        </div>
                    </div>

                    {{-- Paid Amount Input --}}
                    <div class="mb-3">
                        <label for="paid_amount_input" class="form-label"><strong>Amount Paid</strong> <span class="text-muted">(Optional)</span></label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text"><strong>Rs.</strong></span>
                            <input type="number" 
                                   class="form-control form-control-lg" 
                                   id="paid_amount_input" 
                                   name="paid_amount"
                                   step="0.01" 
                                   min="0" 
                                   max="{{ $sale->total_amount }}"
                                   value="0" 
                                   placeholder="0.00"
                                   onkeyup="calculatePaymentDue()"
                                   oninput="calculatePaymentDue()">
                        </div>
                        <small class="text-muted d-block mt-2">Enter amount received from customer (leave 0 if to be collected later)</small>
                    </div>

                    {{-- Due Amount Display --}}
                    <div class="alert alert-info mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><strong>Due Amount (To Collect):</strong></span>
                            <strong class="h5 mb-0" id="due_amount_display">Rs. {{ number_format($sale->total_amount, 2) }}</strong>
                        </div>
                    </div>

                    {{-- Payment Status Display --}}
                    <div id="payment_status_badge" class="alert alert-light mb-3">
                        <small><strong>Payment Status:</strong> <span id="status_text">Unpaid</span></small>
                    </div>

                    <hr>

                    {{-- Payment Details (shown only if amount > 0) --}}
                    <div id="payment_fields_container" style="display: none;">
                        <h6 class="mb-3">Payment Details</h6>
                        
                        {{-- Payment Method --}}
                        <div class="mb-3">
                            <label for="payment_method" class="form-label"><strong>Payment Method</strong> <span class="text-danger">*</span></label>
                            <select class="form-select" id="payment_method" name="payment_method">
                                <option value="">-- Select Payment Method --</option>
                                @foreach(\App\Models\Payment::$methods as $key => $label)
                                    <option value="{{ $key }}" {{ $key === 'cash' ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Reference Number --}}
                        <div class="mb-3">
                            <label for="reference_number" class="form-label">Reference Number <span class="text-muted">(Optional)</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="reference_number" 
                                   name="reference_number" 
                                   maxlength="100"
                                   placeholder="Transaction ID, Cheque No., etc.">
                            <small class="text-muted">Enter transaction ID, cheque number, or other reference</small>
                        </div>

                        {{-- Payment Notes --}}
                        <div class="mb-3">
                            <label for="payment_notes" class="form-label">Payment Notes <span class="text-muted">(Optional)</span></label>
                            <textarea class="form-control" 
                                      id="payment_notes" 
                                      name="payment_notes" 
                                      rows="2" 
                                      maxlength="500"
                                      placeholder="Additional notes about the payment"></textarea>
                        </div>

                        <hr>
                    </div>

                    <div class="alert alert-warning mb-0">
                        <small>
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <strong>Stock Impact:</strong> Confirming this sale will reduce stock from <strong>{{ $sale->warehouse->name }}</strong>.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-check-circle me-1"></i> Confirm & Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function checkStockAvailability() {
    const productId = document.getElementById('product_id').value;
    const quantityInput = document.getElementById('quantity');
    const stockAlert = document.getElementById('stock_alert');
    const stockWarning = document.getElementById('stock_warning');
    
    if (productId) {
        // Get unit price from selected option
        const option = document.querySelector(`#product_id option[value="${productId}"]`);
        const unitPrice = option.dataset.price;
        const warehouseStock = parseFloat(option.dataset.stock) || 0;
        
        document.getElementById('unit_price').value = unitPrice;
        
        // Show stock alert with the stock from data attribute (no AJAX needed)
        stockAlert.classList.remove('d-none');
        document.getElementById('stock_available_text').textContent = warehouseStock;
        
        // Add validation warning if user enters quantity
        quantityInput.addEventListener('input', function() {
            const requestedQty = parseFloat(this.value) || 0;
            if (requestedQty > warehouseStock) {
                stockWarning.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Insufficient stock! Only ${warehouseStock} units available.</span>`;
                this.classList.add('is-invalid');
            } else if (requestedQty > 0) {
                stockWarning.innerHTML = `<span class="text-success"><i class="bi bi-check-circle"></i> OK</span>`;
                this.classList.remove('is-invalid');
            } else {
                stockWarning.innerHTML = '';
                this.classList.remove('is-invalid');
            }
        });
    } else {
        // Hide alert if no product selected
        stockAlert.classList.add('d-none');
        document.getElementById('unit_price').value = '';
        quantityInput.value = '';
        quantityInput.classList.remove('is-invalid');
        stockWarning.innerHTML = '';
    }
}

// Check stock when modal is opened
document.addEventListener('show.bs.modal', function(event) {
    if (event.target.id === 'addItemModal') {
        // Reset form
        document.getElementById('product_id').value = '';
        document.getElementById('quantity').value = '';
        document.getElementById('unit_price').value = '';
        document.getElementById('discount').value = '0';
        document.getElementById('stock_alert').classList.add('d-none');
        document.getElementById('stock_warning').innerHTML = '';
    }
});

// Prevent form submission if product is out of stock or quantity exceeds available
document.addEventListener('submit', function(event) {
    if (event.target.getAttribute('action')?.includes('addItem')) {
        const productId = document.getElementById('product_id').value;
        const quantity = parseFloat(document.getElementById('quantity').value) || 0;
        
        if (!productId) {
            event.preventDefault();
            alert('Please select a product.');
            return false;
        }
        
        const option = document.querySelector(`#product_id option[value="${productId}"]`);
        if (!option) {
            event.preventDefault();
            alert('Invalid product selected.');
            return false;
        }
        
        const warehouseStock = parseFloat(option.dataset.stock) || 0;
        
        // Check if product is disabled
        if (option.disabled) {
            event.preventDefault();
            alert('Cannot add out-of-stock products. Please select a product with available stock.');
            return false;
        }
        
        // Check if quantity exceeds available stock
        if (quantity > warehouseStock) {
            event.preventDefault();
            alert(`Insufficient stock! Only ${warehouseStock} units available.`);
            return false;
        }
        
        if (quantity <= 0) {
            event.preventDefault();
            alert('Please enter a valid quantity.');
            return false;
        }
    }
});

// Payment Calculation Function
const totalAmountForPayment = {{ $sale->total_amount }};

function calculatePaymentDue() {
    const paidAmountInput = document.getElementById('paid_amount_input');
    const paidAmount = parseFloat(paidAmountInput.value) || 0;
    const dueAmount = totalAmountForPayment - paidAmount;
    
    // Validate paid amount does not exceed total
    if (paidAmount > totalAmountForPayment) {
        paidAmountInput.value = totalAmountForPayment;
        alert('Paid amount cannot exceed total amount!');
        calculatePaymentDue();
        return;
    }
    
    // Update due amount display
    document.getElementById('due_amount_display').textContent = 'Rs. ' + dueAmount.toFixed(2);
    
    // Update payment status
    let statusText = 'Unpaid';
    let badgeClass = 'alert-light';
    
    if (paidAmount >= totalAmountForPayment) {
        statusText = 'Fully Paid';
        badgeClass = 'alert-success';
    } else if (paidAmount > 0) {
        statusText = 'Partially Paid';
        badgeClass = 'alert-warning';
    }
    
    document.getElementById('status_text').textContent = statusText;
    const badge = document.getElementById('payment_status_badge');
    badge.className = 'alert ' + badgeClass + ' mb-3';
    
    // Show/hide payment fields based on amount
    const paymentFieldsContainer = document.getElementById('payment_fields_container');
    const paymentMethodSelect = document.getElementById('payment_method');
    
    if (paidAmount > 0) {
        paymentFieldsContainer.style.display = 'block';
        paymentMethodSelect.required = true;
    } else {
        paymentFieldsContainer.style.display = 'none';
        paymentMethodSelect.required = false;
    }
}

// Initialize when modal opens
document.addEventListener('show.bs.modal', function(event) {
    if (event.target.id === 'confirmWithPaymentModal') {
        document.getElementById('paid_amount_input').value = '0';
        document.getElementById('payment_method').value = 'cash';
        document.getElementById('reference_number').value = '';
        document.getElementById('payment_notes').value = '';
        calculatePaymentDue();
    }
});
</script>

<style>
/* Style for disabled product options - lighter/grayed out appearance */
select option:disabled {
    color: #999 !important;
    background-color: #f5f5f5 !important;
    opacity: 0.6 !important;
}

select option {
    padding: 8px;
}

select option[disabled] {
    font-style: italic;
    text-decoration: line-through;
}
</style>
@endpush
@endsection
