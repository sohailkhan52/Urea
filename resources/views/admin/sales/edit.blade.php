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
    @else
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>Validation Errors</h5>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @endif

    <form action="{{ route('admin.sales.updateWithItems', $sale) }}" method="POST" id="sale-form">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Left Column: Sale Header & Items --}}
            <div class="col-lg-8">
                {{-- SALE HEADER SECTION --}}
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Sale Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Customer --}}
                            <div class="col-md-6">
                                <label for="customer_id" class="form-label">Customer <span class="text-muted">(Optional)</span></label>
                                <select class="form-select @error('customer_id') is-invalid @enderror" 
                                        id="customer_id" 
                                        name="customer_id">
                                    <option value="">-- Walk-in Customer --</option>
                                    @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ $sale->customer_id == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} ({{ $customer->customer_type }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Walk-in Customer Name --}}
                            <div class="col-md-6" id="walkin-name-field" style="display: none;">
                                <label for="walkin_customer_name" class="form-label">Walk-in Customer Name</label>
                                <input type="text" 
                                       class="form-control @error('walkin_customer_name') is-invalid @enderror" 
                                       id="walkin_customer_name" 
                                       name="walkin_customer_name" 
                                       value="{{ $sale->walkin_customer_name ?? '' }}"
                                       placeholder="Enter customer name"
                                       maxlength="100">
                                @error('walkin_customer_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Walk-in Customer Contact --}}
                            <div class="col-md-6" id="walkin-contact-field" style="display: none;">
                                <label for="walkin_customer_contact" class="form-label">Walk-in Customer Contact</label>
                                <input type="text" 
                                       class="form-control @error('walkin_customer_contact') is-invalid @enderror" 
                                       id="walkin_customer_contact" 
                                       name="walkin_customer_contact" 
                                       value="{{ $sale->walkin_customer_contact ?? '' }}"
                                       placeholder="Enter phone/contact"
                                       maxlength="50">
                                @error('walkin_customer_contact')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Warehouse (LOCKED) --}}
                            <div class="col-md-6">
                                <label class="form-label">Warehouse</label>
                                <input type="text" class="form-control" value="{{ $sale->warehouse->name }}" disabled>
                                <small class="text-muted">Warehouse cannot be changed for existing sales</small>
                            </div>

                            {{-- Sale Date --}}
                            <div class="col-md-6">
                                <label for="sale_date" class="form-label">Sale Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('sale_date') is-invalid @enderror" 
                                       id="sale_date" 
                                       name="sale_date" 
                                       value="{{ $sale->sale_date->format('Y-m-d') }}"
                                       required>
                                @error('sale_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Status Display --}}
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <div class="form-control-plaintext pt-2">
                                    <span class="badge bg-warning"><i class="bi bi-exclamation-circle me-1"></i> Draft</span>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="col-12">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" 
                                          name="notes" 
                                          rows="2"
                                          placeholder="Add any additional notes..."
                                          maxlength="1000">{{ $sale->notes ?? '' }}</textarea>
                                <small class="text-muted d-block mt-1"><span id="notes-count">{{ strlen($sale->notes ?? '') }}</span>/1000 characters</small>
                                @error('notes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SALE ITEMS SECTION --}}
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Sale Items</h5>
                        <button type="button" class="btn btn-sm btn-primary" id="add-row-btn" title="Add a new product to the sale">
                            <i class="bi bi-plus-circle me-1"></i> Add Item
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0" id="items-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 25%;">Product</th>
                                        <th style="width: 12%;">Available</th>
                                        <th style="width: 12%;">Quantity</th>
                                        <th style="width: 13%;">Unit Price</th>
                                        <th style="width: 13%;">Discount</th>
                                        <th style="width: 15%;" class="text-end">Subtotal</th>
                                        <th style="width: 10%;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="items-tbody">
                                    <!-- Rows will be added here -->
                                </tbody>
                            </table>
                            <div id="no-items-message" class="text-center text-muted py-5">
                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                <p class="mt-3">No items added yet. Click "Add Item" to start.</p>
                            </div>
                        </div>
                        @error('items')
                        <div class="alert alert-danger mt-3 mb-0"><i class="bi bi-exclamation-circle me-2"></i>{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Right Column: Summary & Actions --}}
            <div class="col-lg-4">
                {{-- SALE SUMMARY SECTION --}}
                <div class="card mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Sale Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Item Count</small>
                                <h6 id="item-count" class="mb-0 fw-bold">0</h6>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Total Qty</small>
                                <h6 id="total-qty" class="mb-0 fw-bold">0.00</h6>
                            </div>
                        </div>

                        <div class="table-sm mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <strong id="subtotal" class="text-dark">Rs. 0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Item Discounts</span>
                                <span id="item-discounts" class="text-danger">- Rs. 0.00</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between mb-3">
                                <span>Sale-Level Discount</span>
                                <span id="sale-discount-display" class="text-danger">- Rs. 0.00</span>
                            </div>
                        </div>

                        <div class="mb-3 border-top pt-3">
                            <label for="sale_discount" class="form-label">Sale-Level Discount (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" 
                                       class="form-control" 
                                       id="sale_discount" 
                                       name="discount" 
                                       value="{{ $sale->discount ?? 0 }}"
                                       min="0" 
                                       step="0.01"
                                       placeholder="0.00">
                            </div>
                        </div>

                        <div class="alert alert-info py-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold"><i class="bi bi-tag me-2"></i>Grand Total</span>
                                <span class="fs-5 fw-bold text-primary" id="grand-total">Rs. 0.00</span>
                            </div>
                        </div>

                        <hr>

                        {{-- Action Buttons --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="save-changes-btn" name="action" value="update">
                                <i class="bi bi-download me-1"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn-success btn-lg" id="confirm-btn" data-bs-toggle="modal" data-bs-target="#confirmWithPaymentModal">
                                <i class="bi bi-check-circle me-1"></i> Confirm Sale
                            </button>
                            <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                        </div>

                        <div class="alert alert-warning mt-3 py-2 small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Tip:</strong> Make changes and save, or confirm to reduce inventory.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
                            <strong class="text-primary h5 mb-0" id="confirm-total-amount">Rs. 0.00</strong>
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
                            <strong class="h5 mb-0" id="due_amount_display">Rs. 0.00</strong>
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

<style>
    @media (max-width: 768px) {
        .sticky-top {
            position: static !important;
        }
        
        .table-responsive {
            font-size: 0.85rem;
        }
        
        .col-lg-4 {
            margin-top: 2rem;
        }
    }
    
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .is-invalid {
        border-color: #dc3545;
    }
    
    .invalid-feedback {
        color: #dc3545;
        font-size: 0.875rem;
    }
</style>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if sale is a draft - only show form if it is
    if (document.querySelector('div.alert-danger')) {
        // If non-draft alert is shown, disable the form
        document.getElementById('sale-form').style.opacity = '0.6';
        document.getElementById('sale-form').style.pointerEvents = 'none';
        return;
    }

    // ============ DOM Elements ============
    const form = document.getElementById('sale-form');
    const customerSelect = document.getElementById('customer_id');
    const walkinNameField = document.getElementById('walkin-name-field');
    const walkinContactField = document.getElementById('walkin-contact-field');
    const addRowBtn = document.getElementById('add-row-btn');
    const itemsTable = document.getElementById('items-table');
    const itemsTbody = document.getElementById('items-tbody');
    const noItemsMessage = document.getElementById('no-items-message');
    const saleDiscountInput = document.getElementById('sale_discount');
    const notesInput = document.getElementById('notes');
    const notesCount = document.getElementById('notes-count');
    
    // Summary elements
    const itemCountDisplay = document.getElementById('item-count');
    const totalQtyDisplay = document.getElementById('total-qty');
    const subtotalDisplay = document.getElementById('subtotal');
    const itemDiscountsDisplay = document.getElementById('item-discounts');
    const saleDiscountDisplay = document.getElementById('sale-discount-display');
    const grandTotalDisplay = document.getElementById('grand-total');

    // ============ State ============
    let itemCount = {{ count($sale->items) }}; // Start from current item count for edit
    let productCache = {}; // Cache for warehouse products
    const warehouseId = {{ $sale->warehouse_id }};
    
    // ============ Customer Toggle ============
    function toggleWalkinFields() {
        if (customerSelect.value === '') {
            walkinNameField.style.display = 'block';
            walkinContactField.style.display = 'block';
        } else {
            walkinNameField.style.display = 'none';
            walkinContactField.style.display = 'none';
            document.getElementById('walkin_customer_name').value = '';
            document.getElementById('walkin_customer_contact').value = '';
        }
    }

    toggleWalkinFields();
    customerSelect.addEventListener('change', toggleWalkinFields);

    // ============ Notes Counter ============
    notesInput.addEventListener('input', function() {
        notesCount.textContent = this.value.length;
    });
    notesCount.textContent = notesInput.value.length;

    // ============ Fetch Products from Warehouse ============
    async function loadProductsForWarehouse(whId) {
        if (!whId) return [];
        
        if (productCache[whId]) {
            return productCache[whId];
        }

        try {
            const response = await fetch(`/admin/sales/warehouse/${whId}/products`);
            if (!response.ok) throw new Error('Failed to load products');
            
            const data = await response.json();
            productCache[whId] = data;
            return data;
        } catch (error) {
            console.error('Error loading products:', error);
            alert('Failed to load products for warehouse');
            return [];
        }
    }

    // ============ Create Row HTML ============
    function createItemRow(rowIndex) {
        const row = document.createElement('tr');
        row.id = `item-row-${rowIndex}`;
        row.className = 'item-row';
        
        row.innerHTML = `
            <td>
                <select class="form-select form-select-sm product-select" data-row="${rowIndex}" required>
                    <option value="">-- Select Product --</option>
                </select>
                <small class="text-danger error-message" style="display:none;"></small>
            </td>
            <td>
                <span class="badge bg-info available-stock">0 units</span>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm quantity-input" 
                       data-row="${rowIndex}" value="1" min="0.01" step="0.01" required>
                <small class="text-danger error-message" style="display:none;"></small>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm unit-price-input" 
                       data-row="${rowIndex}" value="0" min="0" step="0.01" required>
                <small class="text-danger error-message" style="display:none;"></small>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm discount-input" 
                       data-row="${rowIndex}" value="0" min="0" step="0.01">
            </td>
            <td class="text-end">
                <strong class="subtotal-display">Rs. 0.00</strong>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger remove-row-btn" data-row="${rowIndex}" title="Remove this item">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        return row;
    }

    // ============ Populate Product Dropdown ============
    async function populateProductDropdown(rowIndex) {
        const products = await loadProductsForWarehouse(warehouseId);
        const select = document.querySelector(`select[data-row="${rowIndex}"]`);
        
        // Clear existing options (keep placeholder)
        select.innerHTML = '<option value="">-- Select Product --</option>';
        
        if (products.length === 0) {
            select.innerHTML += '<option value="" disabled>No products with stock</option>';
            return;
        }
        
        products.forEach(product => {
            const option = document.createElement('option');
            option.value = product.id;
            option.textContent = `${product.name} (${product.sku}) — ${product.available_stock} units`;
            option.dataset.price = product.sale_price;
            option.dataset.stock = product.available_stock;
            select.appendChild(option);
        });
    }

    // ============ Add Row ============
    addRowBtn.addEventListener('click', async function() {
        const newRow = createItemRow(itemCount);
        itemsTbody.appendChild(newRow);
        
        await populateProductDropdown(itemCount);
        attachRowEventListeners(itemCount);
        
        itemCount++;
        updateNoItemsMessage();
        updateSummary();
    });

    // ============ Attach Row Event Listeners ============
    function attachRowEventListeners(rowIndex) {
        const row = document.getElementById(`item-row-${rowIndex}`);
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const unitPriceInput = row.querySelector('.unit-price-input');
        const discountInput = row.querySelector('.discount-input');
        const removeBtn = row.querySelector('.remove-row-btn');

        // Product selection
        productSelect.addEventListener('change', function() {
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const unitPrice = selectedOption.dataset.price || 0;
            const availableStock = selectedOption.dataset.stock || 0;

            // Update unit price
            unitPriceInput.value = parseFloat(unitPrice).toFixed(2);

            // Update available stock display
            row.querySelector('.available-stock').textContent = `${availableStock} units`;

            clearValidationError(row, 'product');
            updateRowSubtotal(rowIndex);
            updateSummary();
        });

        // Quantity change
        quantityInput.addEventListener('change', function() {
            clearValidationError(row, 'quantity');
            validateQuantityAgainstStock(rowIndex);
            updateRowSubtotal(rowIndex);
            updateSummary();
        });

        quantityInput.addEventListener('input', function() {
            updateRowSubtotal(rowIndex);
            updateSummary();
        });

        // Unit price change
        unitPriceInput.addEventListener('change', function() {
            clearValidationError(row, 'unit_price');
            updateRowSubtotal(rowIndex);
            updateSummary();
        });

        unitPriceInput.addEventListener('input', function() {
            updateRowSubtotal(rowIndex);
            updateSummary();
        });

        // Discount change
        discountInput.addEventListener('input', function() {
            updateRowSubtotal(rowIndex);
            updateSummary();
        });

        // Remove row
        removeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            row.remove();
            updateNoItemsMessage();
            updateSummary();
        });
    }

    // ============ Validation ============
    function validateQuantityAgainstStock(rowIndex) {
        const row = document.getElementById(`item-row-${rowIndex}`);
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const quantity = parseFloat(quantityInput.value) || 0;
        const availableStock = parseFloat(productSelect.options[productSelect.selectedIndex].dataset.stock) || 0;

        const errorMsg = row.querySelector('.quantity-input').parentElement.querySelector('.error-message');
        
        if (quantity > availableStock) {
            errorMsg.textContent = `Only ${availableStock} units available`;
            errorMsg.style.display = 'block';
            quantityInput.classList.add('is-invalid');
            return false;
        } else {
            errorMsg.style.display = 'none';
            quantityInput.classList.remove('is-invalid');
            return true;
        }
    }

    function clearValidationError(row, fieldType) {
        const input = row.querySelector(`.${fieldType}-input, .product-select`);
        if (input) {
            const errorMsg = input.parentElement.querySelector('.error-message');
            if (errorMsg) {
                errorMsg.style.display = 'none';
            }
            input.classList.remove('is-invalid');
        }
    }

    // ============ Calculate Row Subtotal ============
    function updateRowSubtotal(rowIndex) {
        const row = document.getElementById(`item-row-${rowIndex}`);
        if (!row) return;

        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.unit-price-input').value) || 0;
        const discount = parseFloat(row.querySelector('.discount-input').value) || 0;

        const subtotal = (quantity * unitPrice) - discount;
        row.querySelector('.subtotal-display').textContent = `Rs. ${Math.max(0, subtotal).toFixed(2)}`;
    }

    // ============ Update No Items Message ============
    function updateNoItemsMessage() {
        if (itemsTbody.children.length === 0) {
            noItemsMessage.style.display = 'block';
            itemsTable.style.display = 'none';
        } else {
            noItemsMessage.style.display = 'none';
            itemsTable.style.display = 'table';
        }
    }

    // ============ Update Summary ============
    function updateSummary() {
        let itemCountVal = 0;
        let totalQtyVal = 0;
        let subtotalVal = 0;
        let itemDiscountsVal = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            itemCountVal++;
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const unitPrice = parseFloat(row.querySelector('.unit-price-input').value) || 0;
            const discount = parseFloat(row.querySelector('.discount-input').value) || 0;

            totalQtyVal += quantity;
            subtotalVal += (quantity * unitPrice);
            itemDiscountsVal += discount;
        });

        const saleDiscount = parseFloat(saleDiscountInput.value) || 0;
        const grandTotal = subtotalVal - itemDiscountsVal - saleDiscount;

        itemCountDisplay.textContent = itemCountVal;
        totalQtyDisplay.textContent = totalQtyVal.toFixed(2);
        subtotalDisplay.textContent = `Rs. ${subtotalVal.toFixed(2)}`;
        itemDiscountsDisplay.textContent = `- Rs. ${itemDiscountsVal.toFixed(2)}`;
        saleDiscountDisplay.textContent = saleDiscount > 0 ? `- Rs. ${saleDiscount.toFixed(2)}` : '- Rs. 0.00';
        grandTotalDisplay.textContent = `Rs. ${Math.max(0, grandTotal).toFixed(2)}`;
        
        // Update confirm modal total
        document.getElementById('confirm-total-amount').textContent = `Rs. ${Math.max(0, grandTotal).toFixed(2)}`;
    }

    // Sale discount change
    saleDiscountInput.addEventListener('input', updateSummary);

    // ============ Load Existing Items (for Edit View) ============
    async function loadExistingItems() {
        const items = @json($sale->items);
        await loadProductsForWarehouse(warehouseId); // Pre-load products
        
        items.forEach((item, idx) => {
            const newRow = createItemRow(idx);
            itemsTbody.appendChild(newRow);
            
            // Populate product dropdown and set values
            const select = newRow.querySelector('.product-select');
            const products = productCache[warehouseId] || [];
            
            select.innerHTML = '<option value="">-- Select Product --</option>';
            products.forEach(product => {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = `${product.name} (${product.sku}) — ${product.available_stock} units`;
                option.dataset.price = product.sale_price;
                option.dataset.stock = product.available_stock;
                if (product.id === item.product_id) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
            
            // Set item values
            newRow.querySelector('.quantity-input').value = item.quantity;
            newRow.querySelector('.unit-price-input').value = item.unit_price;
            newRow.querySelector('.discount-input').value = item.discount;
            newRow.querySelector('.available-stock').textContent = `${products.find(p => p.id === item.product_id)?.available_stock || 0} units`;
            
            attachRowEventListeners(idx);
            updateRowSubtotal(idx);
        });
        
        updateNoItemsMessage();
        updateSummary();
    }

    // Load existing items on page load
    loadExistingItems();

    // ============ Form Submission ============
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validation
        if (itemsTbody.children.length === 0) {
            alert('Please add at least one product item');
            addRowBtn.focus();
            return;
        }

        // Validate all rows
        let hasErrors = false;
        document.querySelectorAll('.item-row').forEach((row, idx) => {
            const productSelect = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.quantity-input');
            const unitPriceInput = row.querySelector('.unit-price-input');
            const quantity = parseFloat(quantityInput.value) || 0;
            const unitPrice = parseFloat(unitPriceInput.value) || 0;

            // Check product selected
            if (!productSelect.value) {
                productSelect.classList.add('is-invalid');
                row.querySelector('.product-select').parentElement.querySelector('.error-message').textContent = 'Product is required';
                row.querySelector('.product-select').parentElement.querySelector('.error-message').style.display = 'block';
                hasErrors = true;
            } else {
                productSelect.classList.remove('is-invalid');
            }

            // Check quantity
            if (quantity <= 0) {
                quantityInput.classList.add('is-invalid');
                const errMsg = quantityInput.parentElement.querySelector('.error-message');
                if (errMsg) {
                    errMsg.textContent = 'Quantity must be greater than 0';
                    errMsg.style.display = 'block';
                }
                hasErrors = true;
            }

            // Check quantity vs stock
            if (!validateQuantityAgainstStock(idx)) {
                hasErrors = true;
            }

            // Check unit price
            if (unitPrice <= 0) {
                unitPriceInput.classList.add('is-invalid');
                const errMsg = unitPriceInput.parentElement.querySelector('.error-message');
                if (errMsg) {
                    errMsg.textContent = 'Unit price must be greater than 0';
                    errMsg.style.display = 'block';
                }
                hasErrors = true;
            } else {
                unitPriceInput.classList.remove('is-invalid');
            }
        });

        if (hasErrors) {
            alert('Please fix the highlighted errors');
            return;
        }

        // Collect item data
        const items = [];
        document.querySelectorAll('.item-row').forEach(row => {
            items.push({
                product_id: row.querySelector('.product-select').value,
                quantity: parseFloat(row.querySelector('.quantity-input').value),
                unit_price: parseFloat(row.querySelector('.unit-price-input').value),
                discount: parseFloat(row.querySelector('.discount-input').value) || 0,
            });
        });

        // Add items to hidden input
        const itemsInput = document.createElement('input');
        itemsInput.type = 'hidden';
        itemsInput.name = 'items';
        itemsInput.value = JSON.stringify(items);
        form.appendChild(itemsInput);

        // Disable submit buttons and show loading state
        const submitButtons = form.querySelectorAll('button[type="submit"]');
        submitButtons.forEach(btn => {
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Processing...';
        });

        // Submit
        form.submit();
    });

    updateNoItemsMessage();
});

// ============ Payment Calculation ============
function calculatePaymentDue() {
    const paidAmountInput = document.getElementById('paid_amount_input');
    const paidAmount = parseFloat(paidAmountInput.value) || 0;
    const totalAmount = parseFloat(document.getElementById('confirm-total-amount').textContent.replace('Rs. ', '')) || 0;
    const dueAmount = totalAmount - paidAmount;
    
    // Validate paid amount does not exceed total
    if (paidAmount > totalAmount) {
        paidAmountInput.value = totalAmount;
        alert('Paid amount cannot exceed total amount!');
        calculatePaymentDue();
        return;
    }
    
    // Update due amount display
    document.getElementById('due_amount_display').textContent = 'Rs. ' + dueAmount.toFixed(2);
    
    // Update payment status
    let statusText = 'Unpaid';
    let badgeClass = 'alert-light';
    
    if (paidAmount >= totalAmount) {
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

// Initialize when confirm modal opens
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
@endpush
