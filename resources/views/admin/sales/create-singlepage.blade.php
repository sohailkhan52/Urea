@extends('layouts.admin')

@section('title', 'Create Sale')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Create Sale</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.sales.index') }}">Sales</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.sales.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

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

    <form action="{{ route('admin.sales.store') }}" method="POST" id="sale-form">
        @csrf

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
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
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
                                       value="{{ old('walkin_customer_name') }}"
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
                                       value="{{ old('walkin_customer_contact') }}"
                                       placeholder="Enter phone/contact"
                                       maxlength="50">
                                @error('walkin_customer_contact')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Warehouse (REQUIRED) --}}
                            <div class="col-md-6">
                                <label for="warehouse_id" class="form-label">Warehouse <span class="text-danger">*</span></label>
                                <select class="form-select @error('warehouse_id') is-invalid @enderror" 
                                        id="warehouse_id" 
                                        name="warehouse_id" 
                                        required>
                                    <option value="">-- Select Warehouse --</option>
                                    @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" 
                                            {{ (old('warehouse_id') == $warehouse->id) || (!old('warehouse_id') && $defaultWarehouse && $defaultWarehouse->id == $warehouse->id) ? 'selected' : '' }}>
                                        {{ $warehouse->name }}{{ $warehouse->is_default ? ' ⭐' : '' }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('warehouse_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Sale Date --}}
                            <div class="col-md-6">
                                <label for="sale_date" class="form-label">Sale Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('sale_date') is-invalid @enderror" 
                                       id="sale_date" 
                                       name="sale_date" 
                                       value="{{ old('sale_date', date('Y-m-d')) }}"
                                       required>
                                @error('sale_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Notes --}}
                            <div class="col-12">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" 
                                          name="notes" 
                                          rows="2"
                                          placeholder="Add any additional notes..."
                                          maxlength="1000">{{ old('notes') }}</textarea>
                                <small class="text-muted d-block mt-1"><span id="notes-count">0</span>/1000 characters</small>
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
                                       value="{{ old('discount', 0) }}"
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

                        {{-- Payment Amount Input --}}
                        <div class="mb-3">
                            <label for="paid_amount" class="form-label">Amount Paid <span class="text-muted">(Optional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" 
                                       class="form-control" 
                                       id="paid_amount" 
                                       name="paid_amount" 
                                       value="{{ old('paid_amount', 0) }}"
                                       min="0" 
                                       step="0.01"
                                       placeholder="0.00">
                            </div>
                            <small class="text-muted d-block mt-1">Enter amount received (0 for unpaid, full amount for paid, or partial)</small>
                        </div>

                        <hr>

                        {{-- Action Buttons --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg" id="confirm-btn" name="action" value="confirm">
                                <i class="bi bi-check-circle me-1"></i> Create & Confirm Sale
                            </button>
                            <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
    
    .product-search-input {
        min-width: 200px;
    }
    
    .input-group-text {
        border-right: none;
    }
    
    .product-search-input {
        border-left: none;
        border-right: none;
    }
    
    .dropdown-toggle {
        border-left: none;
    }
    
    .input-group:focus-within .input-group-text,
    .input-group:focus-within .dropdown-toggle {
        border-color: #86b7fe;
    }
    
    .product-dropdown-list {
        margin-top: 2px;
        max-height: 200px !important; /* Show ~4 items (50px each) */
        overflow-y: auto;
    }
    
    .product-dropdown-list .dropdown-item {
        border-bottom: 1px solid #f0f0f0;
        min-height: 50px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .product-dropdown-list .dropdown-item:last-child {
        border-bottom: none;
    }
    
    /* Scrollbar styling for better UX */
    .product-dropdown-list::-webkit-scrollbar {
        width: 8px;
    }
    
    .product-dropdown-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .product-dropdown-list::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .product-dropdown-list::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============ DOM Elements ============
    const form = document.getElementById('sale-form');
    const customerSelect = document.getElementById('customer_id');
    const warehouseSelect = document.getElementById('warehouse_id');
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
    let itemCount = 0;
    let productCache = {}; // Cache for warehouse products
    
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

    // ============ Warehouse Change Handler ============
    warehouseSelect.addEventListener('change', function() {
        if (itemsTbody.children.length > 0) {
            if (confirm('Changing warehouse will clear all items. Continue?')) {
                itemsTbody.innerHTML = '';
                itemCount = 0;
                updateNoItemsMessage();
                updateSummary();
            } else {
                warehouseSelect.value = warehouseSelect.dataset.previousValue || '';
                return;
            }
        }
        if (warehouseSelect.value) {
            warehouseSelect.dataset.previousValue = warehouseSelect.value;
        }
    });

    warehouseSelect.dataset.previousValue = warehouseSelect.value;

    // ============ Fetch Products from Warehouse ============
    async function loadProductsForWarehouse(warehouseId) {
        if (!warehouseId) return [];
        
        if (productCache[warehouseId]) {
            return productCache[warehouseId];
        }

        try {
            const response = await fetch(`/admin/sales/warehouse/${warehouseId}/products`);
            if (!response.ok) throw new Error('Failed to load products');
            
            const data = await response.json();
            productCache[warehouseId] = data;
            return data;
        } catch (error) {
            console.error('Error loading products:', error);
            alert('Failed to load products for selected warehouse');
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
                <div class="input-group input-group-sm position-relative">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" 
                           class="form-control product-search-input" 
                           data-row="${rowIndex}" 
                           placeholder="Type to search or select..."
                           autocomplete="off">
                    <input type="hidden" class="product-id-input" data-row="${rowIndex}">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-row="${rowIndex}"></button>
                    <div class="product-dropdown-list" data-row="${rowIndex}" style="display:none; position:absolute; top:100%; left:0; right:0; z-index:1000; background:white; border:1px solid #ddd; border-radius:0.25rem; max-height:200px; overflow-y:auto; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    </div>
                </div>
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

    // ============ Get Selected Product IDs ============
    function getSelectedProductIds() {
        const selectedIds = [];
        document.querySelectorAll('.product-id-input').forEach(input => {
            if (input.value) {
                selectedIds.push(parseInt(input.value));
            }
        });
        return selectedIds;
    }

    // ============ Filter Products (Exclude Already Selected) ============
    function filterAvailableProducts(products) {
        const selectedIds = getSelectedProductIds();
        return products.filter(product => !selectedIds.includes(product.id));
    }

    // ============ Setup Searchable Dropdown ============
    async function setupProductDropdown(rowIndex) {
        const products = await loadProductsForWarehouse(warehouseSelect.value);
        const row = document.getElementById(`item-row-${rowIndex}`);
        const searchInput = row.querySelector('.product-search-input');
        const productIdInput = row.querySelector('.product-id-input');
        const dropdownList = row.querySelector('.product-dropdown-list');
        const dropdownToggle = row.querySelector('.dropdown-toggle');
        const availableStock = row.querySelector('.available-stock');
        const unitPriceInput = row.querySelector('.unit-price-input');

        // Render dropdown items
        function renderDropdown(searchTerm = '') {
            const availableProducts = filterAvailableProducts(products);
            const filtered = searchTerm 
                ? availableProducts.filter(product => 
                    product.name.toLowerCase().includes(searchTerm.toLowerCase()) || 
                    product.sku.toLowerCase().includes(searchTerm.toLowerCase())
                  )
                : availableProducts;

            dropdownList.innerHTML = '';

            if (filtered.length === 0) {
                dropdownList.innerHTML = '<div class="dropdown-item text-muted">No products found</div>';
                return;
            }

            filtered.forEach(product => {
                const item = document.createElement('div');
                item.className = 'dropdown-item';
                item.style.cursor = 'pointer';
                item.style.padding = '0.5rem 1rem';
                item.innerHTML = `
                    <div><strong>${product.name}</strong></div>
                    <small class="text-muted">${product.sku} — ${product.available_stock} units</small>
                `;
                
                item.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f8f9fa';
                });
                
                item.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = 'white';
                });
                
                // Use mousedown instead of click to prevent blur event
                item.addEventListener('mousedown', function(e) {
                    e.preventDefault(); // Prevent blur event
                    selectProduct(product);
                });
                
                dropdownList.appendChild(item);
            });
        }

        // Select a product
        function selectProduct(product) {
            searchInput.value = `${product.name} (${product.sku})`;
            productIdInput.value = product.id;
            productIdInput.dataset.price = product.sale_price;
            productIdInput.dataset.stock = product.available_stock;
            
            availableStock.textContent = `${product.available_stock} units`;
            unitPriceInput.value = parseFloat(product.sale_price).toFixed(2);
            
            dropdownList.style.display = 'none';
            
            updateRowSubtotal(rowIndex);
            updateSummary();
            clearValidationError(row, 'product');
        }

        // Show dropdown
        function showDropdown() {
            renderDropdown(searchInput.value);
            dropdownList.style.display = 'block';
        }

        // Hide dropdown
        function hideDropdown() {
            setTimeout(() => {
                dropdownList.style.display = 'none';
            }, 200);
        }

        // Search input events
        searchInput.addEventListener('focus', showDropdown);
        searchInput.addEventListener('blur', hideDropdown);
        searchInput.addEventListener('input', function() {
            renderDropdown(this.value);
            dropdownList.style.display = 'block';
        });

        // Dropdown toggle button
        dropdownToggle.addEventListener('mousedown', function(e) {
            e.preventDefault(); // Prevent blur on input
            if (dropdownList.style.display === 'none') {
                searchInput.focus();
                showDropdown();
            } else {
                dropdownList.style.display = 'none';
            }
        });

        // Initial render
        renderDropdown();
    }

    // ============ Add Row ============
    addRowBtn.addEventListener('click', async function() {
        if (!warehouseSelect.value) {
            alert('Please select a warehouse first');
            warehouseSelect.focus();
            return;
        }

        const newRow = createItemRow(itemCount);
        itemsTbody.appendChild(newRow);
        
        await setupProductDropdown(itemCount);
        attachRowEventListeners(itemCount);
        
        itemCount++;
        updateNoItemsMessage();
        updateSummary();
    });

    // ============ Attach Row Event Listeners ============
    function attachRowEventListeners(rowIndex) {
        const row = document.getElementById(`item-row-${rowIndex}`);
        const productIdInput = row.querySelector('.product-id-input');
        const quantityInput = row.querySelector('.quantity-input');
        const unitPriceInput = row.querySelector('.unit-price-input');
        const discountInput = row.querySelector('.discount-input');
        const removeBtn = row.querySelector('.remove-row-btn');

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
        const productIdInput = row.querySelector('.product-id-input');
        const quantityInput = row.querySelector('.quantity-input');
        const quantity = parseFloat(quantityInput.value) || 0;
        const availableStock = parseFloat(productIdInput.dataset.stock) || 0;

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
    }

    // Sale discount change
    saleDiscountInput.addEventListener('input', updateSummary);

    // ============ Form Submission ============
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validation
        if (itemsTbody.children.length === 0) {
            alert('Please add at least one product item');
            addRowBtn.focus();
            return;
        }

        if (!warehouseSelect.value) {
            alert('Please select a warehouse');
            warehouseSelect.focus();
            return;
        }

        // Validate all rows
        let hasErrors = false;
        document.querySelectorAll('.item-row').forEach((row, idx) => {
            const productIdInput = row.querySelector('.product-id-input');
            const productSearchInput = row.querySelector('.product-search-input');
            const quantityInput = row.querySelector('.quantity-input');
            const unitPriceInput = row.querySelector('.unit-price-input');
            const quantity = parseFloat(quantityInput.value) || 0;
            const unitPrice = parseFloat(unitPriceInput.value) || 0;

            // Check product selected
            if (!productIdInput.value) {
                productSearchInput.classList.add('is-invalid');
                const errMsg = row.querySelector('.error-message');
                if (errMsg) {
                    errMsg.textContent = 'Product is required';
                    errMsg.style.display = 'block';
                }
                hasErrors = true;
            } else {
                productSearchInput.classList.remove('is-invalid');
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
                product_id: row.querySelector('.product-id-input').value,
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
</script>
@endpush

