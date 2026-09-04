@extends('layouts.admin')

@section('content')

<div class="container-fluid">

    <div class="page-header mb-4">

        <div class="row align-items-center">

            <div class="col">

                <h1 class="page-title">Create Sale</h1>

            </div>

            <div class="col-auto">

                <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">

                    <i class="bi bi-arrow-left me-1"></i> Back

                </a>

            </div>

        </div>

    </div>

    @if ($errors->any())

        <div class="alert alert-danger alert-dismissible fade show">

            <strong>Error!</strong> Please fix the following:

            <ul class="mb-0 mt-2">

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div>

    @endif

    <form id="saleForm" action="{{ route('admin.sales.store') }}" method="POST">

        @csrf

        <input type="hidden" name="warehouse_id" value="{{ $defaultWarehouse->id }}">

        <input type="hidden" name="sale_date" value="{{ date('Y-m-d') }}">

        <input type="hidden" id="customer_id" name="customer_id" value="">

        <input type="hidden" id="items" name="items">

        <div class="row">

            <!-- LEFT SIDE: 75% -->

            <div class="col-lg-9">

                

                <!-- CUSTOMER SECTION -->

                <div class="card mb-4">

                    <div class="card-header bg-light">

                        <h5 class="mb-0">

                            <i class="bi bi-person-circle"></i> Customer <span class="text-danger">*</span>

                        </h5>

                    </div>

                    <div class="card-body">

                        <div class="row mb-3">

                            <!-- Walk-in Customer Name -->

                            <div class="col-md-6">

                                <label class="form-label">Walk-in Customer Name</label>

                                <input type="text" id="walkin_name" class="form-control" placeholder="Enter name">

                            </div>

                            <!-- Walk-in Phone -->

                            <div class="col-md-6">

                                <label class="form-label">Phone (Optional)</label>

                                <input type="text" id="walkin_phone" class="form-control" placeholder="03XXXXXXXXX">

                            </div>

                        </div>

                        <!-- Customer Select Dropdown -->

                        <div class="mb-3">

                            <label class="form-label">Or Select Existing Customer</label>

                            <select id="existingCustomerSelect" class="form-select">

                                <option value="">-- Search & Select Customer --</option>

                            </select>

                        </div>

                        <!-- Selected Customer Display -->

                        <div id="selectedCustomerCard" style="display: none;" class="alert alert-info mb-0">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <strong id="selectedCustomerName"></strong><br>

                                    <small id="selectedCustomerPhone" class="text-muted"></small>

                                </div>

                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearCustomer()">

                                    <i class="bi bi-x-circle"></i> Change

                                </button>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- FAMILY SECTION (OPTIONAL) -->

                <div class="card mb-4">

                    <div class="card-header bg-light">

                        <h5 class="mb-0">

                            <i class="bi bi-collection"></i> Family (Optional)

                        </h5>

                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-9">

                                <select id="family_id" name="family_id" class="form-select">

                                    <option value="">-- Select Family --</option>

                                    @foreach($families ?? [] as $family)

                                        <option value="{{ $family->id }}">{{ $family->name }}</option>

                                    @endforeach

                                </select>

                            </div>

                            <div class="col-md-3">

                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newFamilyModal">

                                    <i class="bi bi-plus-lg"></i> Add

                                </button>

                            </div>

                        </div>

                        <div id="familyInfo" style="display: none; margin-top: 10px;" class="alert alert-light mb-0">

                            <small class="text-muted">Selected: <strong id="familyName"></strong></small>

                        </div>

                    </div>

                </div>

                <!-- PRODUCTS SECTION -->

                <div class="card mb-4">

                    <div class="card-header bg-light">

                        <h5 class="mb-0">

                            <i class="bi bi-search"></i> Products

                        </h5>

                    </div>

                    <div class="card-body">

                        <div class="mb-3" style="position: relative;">

                            <input type="text" 

                                   id="productSearch" 

                                   class="form-control" 

                                   placeholder="Search Product..."

                                   autocomplete="off">

                            <div id="productDropdown" 

                                 class="position-absolute bg-white border rounded-bottom mt-1 w-100" 

                                 style="display: none; max-height: 250px; overflow-y: auto; z-index: 1000; top: 38px;">

                            </div>

                        </div>

                        

                        <div>

                            <label class="form-label small text-muted mb-2">Quick Add:</label>

                            <div id="existingProducts" class="d-flex flex-wrap gap-2">

                                @foreach($productsWithStock ?? [] as $product)

                                    <button type="button" class="btn btn-outline-secondary btn-sm" 

                                            onclick="addProduct({{ $product->id }}, '{{ $product->name }}', {{ $product->stock ?? 0 }}, {{ $product->sale_price ?? 0 }}, '{{ $product->unit ?? 'Piece' }}')">

                                        {{ $product->name }}

                                    </button>

                                @endforeach

                            </div>

                        </div>

                    </div>

                </div>

                <!-- SALE ITEMS TABLE -->

                <div class="card">

                    <div class="card-header bg-light">

                        <h5 class="mb-0">

                            <i class="bi bi-cart"></i> Sale Items

                        </h5>

                    </div>

                    <div class="card-body">

                        <div class="table-responsive">

                            <table class="table table-sm table-hover mb-0">

                                <thead class="table-light">

                                    <tr>

                                        <th>Product</th>

                                        <th class="text-center" style="width: 80px;">Stock</th>

                                        <th class="text-center" style="width: 70px;">Qty</th>

                                        <th style="width: 70px;">Unit</th>

                                        <th class="text-end" style="width: 100px;">Price</th>

                                        <th class="text-end" style="width: 100px;">Total</th>

                                        <th class="text-center" style="width: 50px;">Action</th>

                                    </tr>

                                </thead>

                                <tbody id="saleItemsTable">

                                    <tr>

                                        <td colspan="7" class="text-center text-muted py-4">

                                            <i class="bi bi-inbox"></i> No items added yet

                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

            <!-- RIGHT SIDE: 25% SIDEBAR -->

            <div class="col-lg-3">

                <div class="card sticky-top" style="top: 20px;">

                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">

                            <i class="bi bi-calculator"></i> Calculation

                        </h5>

                    </div>

                    <div class="card-body">

                        <!-- Subtotal -->

                        <div class="row mb-2">

                            <div class="col-6">

                                <small class="text-muted">Subtotal</small>

                            </div>

                            <div class="col-6 text-end">

                                <small><strong>Rs. <span id="subtotal">0.00</span></strong></small>

                            </div>

                        </div>

                        <!-- Discount Input -->

                        <div class="mb-3">

                            <label class="form-label small mb-1">Discount</label>

                            <input type="number" id="discount" name="discount" class="form-control form-control-sm" 

                                   placeholder="0" step="0.01" min="0" onchange="calculateTotal()">

                        </div>

                        <hr class="my-2">

                        <!-- Breakdown Summary -->

                        <div class="mb-3">

                            <div class="row mb-1">

                                <div class="col-6"><small>Subtotal:</small></div>

                                <div class="col-6 text-end"><small>Rs. <span id="summary_subtotal">0.00</span></small></div>

                            </div>

                            <div class="row mb-1">

                                <div class="col-6"><small>Discount:</small></div>

                                <div class="col-6 text-end"><small>-Rs. <span id="summary_discount">0.00</span></small></div>

                            </div>

                        </div>

                        <hr class="my-2">

                        <!-- Total -->

                        <div class="row mb-3">

                            <div class="col-6">

                                <strong>Total Payment</strong>

                            </div>

                            <div class="col-6 text-end">

                                <strong>Rs. <span id="total">0.00</span></strong>

                            </div>

                        </div>

                        <hr class="my-2">

                        <!-- Paid Amount -->

                        <div class="mb-3">

                            <label class="form-label small mb-1">Paid Amount</label>

                            <input type="number" id="paid_amount" name="paid_amount" class="form-control form-control-sm" 

                                   placeholder="0" step="0.01" min="0" onchange="calculateRemaining()" required>

                        </div>

                        <!-- Remaining Udhar -->

                        <div class="mb-3">

                            <label class="form-label small mb-1">Remaining Udhar</label>

                            <input type="text" id="remaining_udhar" class="form-control form-control-sm text-end" 

                                   value="0.00" readonly style="background-color: #f8f9fa; font-weight: bold;">

                        </div>

                        <!-- Payment Status -->

                        <div class="mb-4">

                            <label class="form-label small mb-1">Payment Status</label>

                            <div class="text-center p-2 rounded" id="paymentStatusBadge" style="background-color: #f8f9fa; font-weight: bold; font-size: 0.9rem;">

                                UNPAID

                            </div>

                        </div>

                        <!-- Save Button -->

                        <button type="submit" class="btn btn-success btn-sm w-100">

                            <i class="bi bi-check-circle me-1"></i> Save Sale

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </form>

</div>

<!-- New Family Modal -->

<div class="modal fade" id="newFamilyModal" tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">Add New Family</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <form id="newFamilyForm">

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">Family Name <span class="text-danger">*</span></label>

                        <input type="text" id="family_name" name="name" class="form-control" required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Description</label>

                        <textarea id="family_description" name="description" class="form-control" rows="2"></textarea>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                    <button type="button" id="saveFamilyBtn" class="btn btn-primary">Create</button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection

@push('scripts')

<script>

let saleItems = {};

let allCustomers = [];

// Load customers on page load

document.addEventListener('DOMContentLoaded', function() {

    loadAllCustomers();

});

// Load all customers and populate dropdown

function loadAllCustomers() {

    fetch('/admin/customers/all', {

        headers: { 'X-Requested-With': 'XMLHttpRequest' }

    })

    .then(res => res.json())

    .then(data => {

        allCustomers = data;

        const select = document.getElementById('existingCustomerSelect');

        data.forEach(c => {

            const option = document.createElement('option');

            option.value = c.id;

            option.textContent = `${c.name}${c.phone ? ' - ' + c.phone : ''}`;

            option.dataset.phone = c.phone || '';

            select.appendChild(option);

        });

        console.log('Loaded', data.length, 'customers');

    })

    .catch(err => console.error('Error loading customers:', err));

}

// Handle dropdown selection

document.getElementById('existingCustomerSelect')?.addEventListener('change', function() {

    if (this.value) {

        const option = this.options[this.selectedIndex];

        selectCustomer(this.value, option.text.split(' -')[0].trim(), option.dataset.phone);

    }

});

// Select customer

function selectCustomer(id, name, phone) {

    document.getElementById('customer_id').value = id;

    document.getElementById('walkin_name').value = '';

    document.getElementById('walkin_phone').value = '';

    document.getElementById('selectedCustomerName').textContent = name;

    document.getElementById('selectedCustomerPhone').textContent = phone || 'No phone';

    document.getElementById('selectedCustomerCard').style.display = 'block';

    // Auto-populate family from customer's family_id
    const customer = allCustomers.find(c => c.id == id);
    if (customer && customer.family_id) {
        document.getElementById('family_id').value = customer.family_id;
        document.getElementById('family_id').dispatchEvent(new Event('change'));
    }

}

// Clear customer selection

function clearCustomer() {

    document.getElementById('customer_id').value = '';

    document.getElementById('selectedCustomerCard').style.display = 'none';

    document.getElementById('existingCustomerSelect').value = '';

    // Also clear family
    document.getElementById('family_id').value = '';
    document.getElementById('familyInfo').style.display = 'none';

}

// Family change handler

document.getElementById('family_id')?.addEventListener('change', function() {

    if (this.value) {

        const selectedOption = this.options[this.selectedIndex];

        document.getElementById('familyName').textContent = selectedOption.text;

        document.getElementById('familyInfo').style.display = 'block';

    } else {

        document.getElementById('familyInfo').style.display = 'none';

    }

});

// Product search

document.getElementById('productSearch').addEventListener('input', function(e) {

    const query = e.target.value.trim();

    if (query.length < 1) {

        document.getElementById('productDropdown').style.display = 'none';

        return;

    }

    

    const warehouseId = document.querySelector('input[name="warehouse_id"]').value;

    

    fetch(`/admin/products/search?search=${encodeURIComponent(query)}&warehouse_id=${warehouseId}`)

        .then(res => res.json())

        .then(data => {

            const dropdown = document.getElementById('productDropdown');

            if (data.length > 0) {

                dropdown.innerHTML = data.map(p => `

                    <div class="p-2 border-bottom" style="cursor: pointer; transition: background-color 0.2s;"

                         onmouseover="this.style.backgroundColor='#f8f9fa';" onmouseout="this.style.backgroundColor='white';"

                         onclick="addProduct(${p.id}, '${p.name.replace(/'/g, "\\'")}', ${p.stock || p.available_stock || 0}, ${p.sale_price || 0}, '${p.unit || 'Piece'}')">

                        <strong>${p.name}</strong> 

                        <span class="badge bg-secondary me-1">${p.unit || 'Piece'}</span>

                        <span class="badge bg-info float-end">Stock: ${p.stock || p.available_stock || 0}</span>

                    </div>

                `).join('');

                dropdown.style.display = 'block';

            } else {

                dropdown.style.display = 'none';

            }

        })

        .catch(err => console.error(err));

});

// Add product

function addProduct(productId, productName, stock = 0, salePrice = 0, unit = 'Piece') {

    if (!saleItems[productId]) {

        saleItems[productId] = {

            id: productId,

            name: productName,

            quantity: 1,

            unit: unit,

            price: salePrice || 0,

            stock: stock

        };

        renderSaleItems();

    }

    document.getElementById('productSearch').value = '';

    document.getElementById('productDropdown').style.display = 'none';

}

// Remove item

function removeSaleItem(productId) {

    delete saleItems[productId];

    renderSaleItems();

}

// Render sale items table

function renderSaleItems() {

    const tbody = document.getElementById('saleItemsTable');

    const items = Object.values(saleItems);

    

    if (items.length === 0) {

        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-inbox"></i> No items added yet</td></tr>';

        calculateTotal();

        return;

    }

    

    tbody.innerHTML = items.map(item => `

        <tr>

            <td><strong>${item.name}</strong></td>

            <td class="text-center">${item.stock}</td>

            <td class="text-center">

                <input type="number" class="form-control form-control-sm text-center" style="width: 60px;" 

                       value="${item.quantity}" min="1" onchange="updateQty(${item.id}, this.value)">

            </td>

            <td>

                <span class="badge bg-secondary">${item.unit}</span>

            </td>

            <td>

                <input type="number" class="form-control form-control-sm text-end" style="width: 100px;" 

                       value="${item.price}" step="0.01" onchange="updatePrice(${item.id}, this.value)">

            </td>

            <td class="text-end"><strong>Rs. ${(item.quantity * item.price).toLocaleString('en-PK', {minimumFractionDigits: 2})}</strong></td>

            <td class="text-center">

                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSaleItem(${item.id})">

                    <i class="bi bi-trash"></i>

                </button>

            </td>

        </tr>

    `).join('');

    

    calculateTotal();

}

// Update quantity

function updateQty(productId, qty) {

    if (saleItems[productId]) {

        saleItems[productId].quantity = parseFloat(qty) || 1;

        renderSaleItems();

    }

}

// Update price

function updatePrice(productId, price) {

    if (saleItems[productId]) {

        saleItems[productId].price = parseFloat(price) || 0;

        renderSaleItems();

    }

}

// Calculate total

function calculateTotal() {

    const subtotal = Object.values(saleItems).reduce((sum, item) => sum + (item.quantity * item.price), 0);

    const discount = parseFloat(document.getElementById('discount').value) || 0;

    const total = subtotal - discount;

    

    document.getElementById('subtotal').textContent = subtotal.toLocaleString('en-PK', {minimumFractionDigits: 2});

    document.getElementById('summary_subtotal').textContent = subtotal.toLocaleString('en-PK', {minimumFractionDigits: 2});

    document.getElementById('summary_discount').textContent = discount.toLocaleString('en-PK', {minimumFractionDigits: 2});

    document.getElementById('total').textContent = total.toLocaleString('en-PK', {minimumFractionDigits: 2});

    

    calculateRemaining();

}

// Calculate remaining

function calculateRemaining() {

    const subtotal = Object.values(saleItems).reduce((sum, item) => sum + (item.quantity * item.price), 0);

    const discount = parseFloat(document.getElementById('discount').value) || 0;

    const total = subtotal - discount;

    const paidAmount = parseFloat(document.getElementById('paid_amount').value) || 0;

    const remaining = total - paidAmount;

    

    document.getElementById('remaining_udhar').value = remaining.toLocaleString('en-PK', {minimumFractionDigits: 2});

    

    const statusBadge = document.getElementById('paymentStatusBadge');

    if (remaining <= 0) {

        statusBadge.textContent = 'PAID';

        statusBadge.style.backgroundColor = '#d4edda';

        statusBadge.style.color = '#155724';

    } else if (paidAmount > 0) {

        statusBadge.textContent = 'PARTIAL';

        statusBadge.style.backgroundColor = '#fff3cd';

        statusBadge.style.color = '#856404';

    } else {

        statusBadge.textContent = 'UNPAID';

        statusBadge.style.backgroundColor = '#f8d7da';

        statusBadge.style.color = '#721c24';

    }

}

// Save family

document.getElementById('saveFamilyBtn')?.addEventListener('click', function() {

    const name = document.getElementById('family_name').value.trim();

    const description = document.getElementById('family_description').value.trim();

    

    if (!name) {

        alert('Please enter family name');

        return;

    }

    

    const formData = new FormData();

    formData.append('name', name);

    if (description) formData.append('description', description);

    

    fetch('/admin/families', {

        method: 'POST',

        body: formData,

        headers: {

            'X-Requested-With': 'XMLHttpRequest',

            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content

        }

    })

    .then(res => res.json())

    .then(data => {

        if (data.success || data.family) {

            const family = data.family || data;

            const select = document.getElementById('family_id');

            const option = document.createElement('option');

            option.value = family.id;

            option.textContent = family.name;

            select.appendChild(option);

            select.value = family.id;

            select.dispatchEvent(new Event('change'));

            document.getElementById('newFamilyForm').reset();

            bootstrap.Modal.getInstance(document.getElementById('newFamilyModal')).hide();

        } else {

            alert('Error: ' + (data.message || 'Unknown error'));

        }

    })

    .catch(err => {

        console.error(err);

        alert('Error creating family');

    });

});

// Form submission

document.getElementById('saleForm').addEventListener('submit', function(e) {

    e.preventDefault();

    

    const customerId = document.getElementById('customer_id').value.trim();

    const walkinName = document.getElementById('walkin_name').value.trim();

    const walkinPhone = document.getElementById('walkin_phone').value.trim();

    

    // Validation - need either a selected customer OR a walk-in name

    if (!customerId && !walkinName) {

        alert('Please either select an existing customer OR enter a walk-in customer name');

        return;

    }

    

    if (Object.keys(saleItems).length === 0) {

        alert('Please add at least one product');

        return;

    }

    

    const items = Object.values(saleItems).map(item => ({

        product_id: item.id,

        quantity: item.quantity,

        unit_price: item.price

    }));

    

    document.getElementById('items').value = JSON.stringify(items);

    

    // If customer is selected, use it directly. Otherwise, create walk-in customer

    if (customerId) {

        // Customer is selected, submit directly

        document.getElementById('saleForm').submit();

    } else if (walkinName) {

        // No customer selected but walk-in name provided, create walk-in customer first

        const formData = new FormData();

        formData.append('name', walkinName);

        formData.append('phone', walkinPhone);

        formData.append('warehouse_id', document.querySelector('input[name="warehouse_id"]').value);

        

        fetch('/admin/customers/ajax', {

            method: 'POST',

            body: formData,

            headers: {

                'X-Requested-With': 'XMLHttpRequest',

                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content

            }

        })

        .then(res => res.json())

        .then(data => {

            if (data.success || data.customer) {

                const customer = data.customer || data;

                document.getElementById('customer_id').value = customer.id;

                document.getElementById('saleForm').submit();

            } else {

                alert('Error creating customer: ' + (data.message || 'Unknown error'));

            }

        })

        .catch(err => {

            console.error(err);

            alert('Error creating customer');

        });

    }

});

</script>

@endpush
