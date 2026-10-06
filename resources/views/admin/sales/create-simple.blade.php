@extends('layouts.admin')

@section('content')

<div class="container-fluid">

    <div class="page-header mb-4">

        <div class="row align-items-center">

            <div class="col">

                <h1 class="page-title">Create Sale</h1>

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

                            <div class="sale-search-wrapper" id="customerSearchWrapper">
                                <input type="text" id="customerSearch" class="form-control" placeholder="Search customer by name or phone..." autocomplete="off">
                                <div id="customerDropdown" class="sale-search-dropdown list-group" style="display: none;"></div>
                                <select id="existingCustomerSelect" class="form-select d-none" aria-hidden="true" tabindex="-1">
                                    <option value="">-- Search & Select Customer --</option>
                                </select>
                            </div>

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

                                <div class="sale-search-wrapper" id="familySearchWrapper">
                                    <input type="text" id="familySearch" class="form-control" placeholder="Search or select family..." autocomplete="off">
                                    <div id="familyDropdown" class="sale-search-dropdown" style="display: none;"></div>
                                    <select id="family_id" name="family_id" class="form-select d-none" aria-hidden="true" tabindex="-1">

                                    <option value="">-- Select Family --</option>

                                    @foreach($families ?? [] as $family)

                                        <option value="{{ $family->id }}">{{ $family->name }}</option>

                                    @endforeach

                                    </select>
                                </div>

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

                        <div class="mb-3 sale-search-wrapper" id="productSearchWrapper">

                            <input type="text" 

                                   id="productSearch" 

                                   class="form-control" 

                                   placeholder="Search Product..."

                                   autocomplete="off">

                            <div id="productDropdown" class="sale-search-dropdown" style="display: none;"></div>

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

                                <small><strong>Rs. <span id="subtotal">0</span></strong></small>

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

                                <div class="col-6 text-end"><small>Rs. <span id="summary_subtotal">0</span></small></div>

                            </div>

                            <div class="row mb-1">

                                <div class="col-6"><small>Discount:</small></div>

                                <div class="col-6 text-end"><small>-Rs. <span id="summary_discount">0</span></small></div>

                            </div>

                        </div>

                        <hr class="my-2">

                        <!-- Total -->

                        <div class="row mb-3">

                            <div class="col-6">

                                <strong>Total Payment</strong>

                            </div>

                            <div class="col-6 text-end">

                                <strong>Rs. <span id="total">0</span></strong>

                            </div>

                        </div>

                        <hr class="my-2">

                        <!-- Paid Amount -->

                        <div class="mb-3">

                            <label class="form-label small mb-1">Paid Amount</label>

                            <input type="text" id="paid_amount" name="paid_amount" class="form-control form-control-sm integer-paid-amount" 

                                   placeholder="0" onchange="calculateRemaining()" onwheel="event.preventDefault()">

                        </div>

                        <!-- Remaining Udhar -->

                        <div class="mb-3">

                            <label class="form-label small mb-1">Remaining Udhar</label>

                            <input type="text" id="remaining_udhar" class="form-control form-control-sm text-end" 

                                   value="0" readonly style="background-color: #f8f9fa; font-weight: bold;">

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

@push('styles')
<style>
    .sale-search-wrapper {
        position: relative;
    }

    .sale-search-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        z-index: 1050;
        max-height: 320px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .sale-search-dropdown .list-group-item {
        border-left: 0;
        border-right: 0;
        padding: 0.75rem 1.25rem;
        line-height: 1.35;
    }

    .sale-search-dropdown .list-group-item:first-child {
        border-top: 0;
    }

    .sale-search-dropdown .list-group-item:last-child {
        border-bottom: 0;
    }

    .sale-search-dropdown .list-group-item-action:hover,
    .sale-search-dropdown .list-group-item-action:focus {
        background-color: #f8f9fa;
    }

    .sale-search-dropdown .list-group-item:disabled {
        color: #6c757d;
        background-color: #f8f9fa;
        opacity: 0.75;
    }
</style>
@endpush

@push('scripts')

<script>

let saleItems = {};

let allCustomers = [];
const allSaleProducts = @json($productsWithStock ?? []);

// Load customers on page load

document.addEventListener('DOMContentLoaded', function() {

    loadAllCustomers();
    
    // Initialize paid amount handler for integer-only input
    const paidAmountInput = document.getElementById('paid_amount');
    if (paidAmountInput) {
        integerPaidAmountHandler(paidAmountInput);
    }

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
        select.innerHTML = '<option value="">-- Search & Select Customer --</option>';

        data.forEach(customer => {
            const option = document.createElement('option');
            option.value = customer.id;
            option.textContent = `${customer.name}${customer.phone ? ' - ' + customer.phone : ''}`;
            option.dataset.phone = customer.phone || '';
            select.appendChild(option);
        });

        if (document.activeElement === document.getElementById('customerSearch')) {
            renderCustomerDropdown(document.getElementById('customerSearch').value);
        }

        console.log('Loaded', data.length, 'customers');

    })

    .catch(err => console.error('Error loading customers:', err));

}

function renderCustomerDropdown(searchTerm = '') {
    const dropdown = document.getElementById('customerDropdown');
    const normalizedTerm = searchTerm.trim().toLowerCase();
    const matches = allCustomers.filter(customer =>
        (customer.name || '').toLowerCase().includes(normalizedTerm) ||
        (customer.phone || '').toLowerCase().includes(normalizedTerm) ||
        (customer.email || '').toLowerCase().includes(normalizedTerm)
    );

    dropdown.innerHTML = '';
    if (matches.length === 0) {
        const empty = document.createElement('div');
        empty.className = 'list-group-item text-muted';
        empty.textContent = allCustomers.length ? 'No customers found' : 'Loading customers...';
        dropdown.appendChild(empty);
    } else {
        matches.slice(0, 30).forEach(customer => {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'list-group-item list-group-item-action text-start';
            const name = document.createElement('span');
            name.className = 'd-block fw-semibold';
            name.textContent = customer.name || '';
            option.appendChild(name);
            if (customer.phone) {
                const phone = document.createElement('small');
                phone.className = 'd-block text-muted';
                phone.textContent = customer.phone;
                option.appendChild(phone);
            }
            option.addEventListener('click', () => selectCustomer(customer.id, customer.name, customer.phone));
            dropdown.appendChild(option);
        });
    }

    dropdown.style.display = 'block';
}

const customerSearch = document.getElementById('customerSearch');
customerSearch.addEventListener('focus', () => renderCustomerDropdown(customerSearch.value));
customerSearch.addEventListener('input', () => {
    const selectedCustomer = allCustomers.find(customer => customer.id == document.getElementById('customer_id').value);
    if (selectedCustomer && customerSearch.value.trim() !== selectedCustomer.name) {
        document.getElementById('customer_id').value = '';
        document.getElementById('existingCustomerSelect').value = '';
        document.getElementById('selectedCustomerCard').style.display = 'none';
        document.getElementById('family_id').value = '';
        document.getElementById('familySearch').value = '';
        document.getElementById('familyInfo').style.display = 'none';
    }
    renderCustomerDropdown(customerSearch.value);
});

document.getElementById('familySearch').addEventListener('focus', function() {
    renderFamilyDropdown(this.value);
});
document.getElementById('familySearch').addEventListener('input', function() {
    const familySelect = document.getElementById('family_id');
    const selectedFamily = familySelect.options[familySelect.selectedIndex];
    if (familySelect.value && selectedFamily.text !== this.value.trim()) {
        familySelect.value = '';
        document.getElementById('familyInfo').style.display = 'none';
    }
    renderFamilyDropdown(this.value);
});

function renderFamilyDropdown(searchTerm = '') {
    const dropdown = document.getElementById('familyDropdown');
    const select = document.getElementById('family_id');
    const normalizedTerm = searchTerm.trim().toLowerCase();
    const options = Array.from(select.options).filter(option =>
        option.value && option.text.toLowerCase().includes(normalizedTerm)
    );

    dropdown.innerHTML = '';
    if (options.length === 0) {
        const empty = document.createElement('div');
        empty.className = 'list-group-item text-muted';
        empty.textContent = 'No families found';
        dropdown.appendChild(empty);
    } else {
        options.slice(0, 30).forEach(family => {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'list-group-item list-group-item-action text-start';
            option.textContent = family.text;
            option.addEventListener('click', () => {
                select.value = family.value;
                document.getElementById('familySearch').value = family.text;
                dropdown.style.display = 'none';
                select.dispatchEvent(new Event('change', { bubbles: true }));
            });
            dropdown.appendChild(option);
        });
    }

    dropdown.style.display = 'block';
}

// Select customer

function selectCustomer(id, name, phone) {

    document.getElementById('customer_id').value = id;

    document.getElementById('existingCustomerSelect').value = id;

    document.getElementById('customerSearch').value = name;

    document.getElementById('customerDropdown').style.display = 'none';

    document.getElementById('walkin_name').value = '';

    document.getElementById('walkin_phone').value = '';

    document.getElementById('selectedCustomerName').textContent = name;

    document.getElementById('selectedCustomerPhone').textContent = phone || 'No phone';

    document.getElementById('selectedCustomerCard').style.display = 'block';

    // Auto-populate family from customer's family_id
    const customer = allCustomers.find(c => c.id == id);
    if (customer && customer.family_id) {
        document.getElementById('family_id').value = customer.family_id;
        document.getElementById('familySearch').value = document.querySelector(`#family_id option[value="${customer.family_id}"]`)?.textContent || '';
        document.getElementById('family_id').dispatchEvent(new Event('change'));
    }

}

// Clear customer selection

function clearCustomer() {

    document.getElementById('customer_id').value = '';

    document.getElementById('selectedCustomerCard').style.display = 'none';

    document.getElementById('existingCustomerSelect').value = '';

    document.getElementById('customerSearch').value = '';

    // Also clear family
    document.getElementById('family_id').value = '';
    document.getElementById('familySearch').value = '';
    document.getElementById('familyInfo').style.display = 'none';

}

// Family change handler

document.getElementById('family_id')?.addEventListener('change', function() {

    if (this.value) {

        const selectedOption = this.options[this.selectedIndex];

        document.getElementById('familySearch').value = selectedOption.text;

        document.getElementById('familyName').textContent = selectedOption.text;

        document.getElementById('familyInfo').style.display = 'block';

    } else {

        document.getElementById('familySearch').value = '';

        document.getElementById('familyInfo').style.display = 'none';

    }

});

// Search all products already loaded for this warehouse on focus and as the user types.
function searchSaleProducts(query = '') {
    const dropdown = document.getElementById('productDropdown');
    const normalizedTerm = query.trim().toLowerCase();
    const products = allSaleProducts.filter(product =>
        (product.name || '').toLowerCase().includes(normalizedTerm) ||
        (product.sku || '').toLowerCase().includes(normalizedTerm)
    );

    dropdown.innerHTML = '';
    if (!products.length) {
        const empty = document.createElement('div');
        empty.className = 'list-group-item text-muted';
        empty.textContent = 'No products found';
        dropdown.appendChild(empty);
    } else {
        products.forEach(product => {
            const option = document.createElement('button');
            const stock = Number(product.stock || 0);
            option.type = 'button';
            option.className = 'list-group-item list-group-item-action text-start';
            option.disabled = stock <= 0;

            const name = document.createElement('span');
            name.className = 'd-block fw-semibold';
            name.textContent = product.name || '';
            option.appendChild(name);

            const meta = document.createElement('small');
            meta.className = 'd-block text-muted';
            meta.textContent = [product.sku, product.unit || 'Piece', `Stock: ${stock}`, `Rs. ${Number(product.sale_price || 0).toLocaleString()}`].filter(Boolean).join(' · ');
            option.appendChild(meta);
            option.addEventListener('click', () => addProduct(product.id, product.name, stock, product.sale_price, product.unit || 'Piece'));
            dropdown.appendChild(option);
        });
    }

    dropdown.style.display = 'block';
}

const productSearch = document.getElementById('productSearch');
productSearch.addEventListener('focus', () => searchSaleProducts(productSearch.value.trim()));
productSearch.addEventListener('input', () => searchSaleProducts(productSearch.value.trim()));

document.addEventListener('click', function(event) {
    if (!event.target.closest('#customerSearchWrapper')) document.getElementById('customerDropdown').style.display = 'none';
    if (!event.target.closest('#familySearchWrapper')) document.getElementById('familyDropdown').style.display = 'none';
    if (!event.target.closest('#productSearchWrapper')) document.getElementById('productDropdown').style.display = 'none';
});

// Add product

function addProduct(productId, productName, stock = 0, salePrice = 0, unit = 'Piece') {

    if (Number(stock) <= 0) {
        focusSaleField(document.getElementById('productSearch'));
        return;
    }

    // Check if customer is selected
    const customerId = document.getElementById('customer_id').value.trim();
    const walkinName = document.getElementById('walkin_name').value.trim();
    
    if (!customerId && !walkinName) {
        focusSaleField(document.getElementById('walkin_name'));
        return;
    }

    if (!saleItems[productId]) {
        // Find full product data from allSaleProducts to get multi-unit information
        const product = allSaleProducts.find(p => p.id === productId);
        const productUnits = product?.product_units || [];
        
        // Determine default unit:
        // 1. If only one unit exists, use it
        // 2. Otherwise, use base unit
        // 3. Fallback: create legacy unit from product data
        let defaultUnit = null;
        
        if (productUnits.length === 1) {
            defaultUnit = productUnits[0];
        } else if (productUnits.length > 1) {
            // Try to find base unit
            defaultUnit = productUnits.find(u => u.is_base_unit) || productUnits[0];
        } else {
            // Legacy product without product_units - create a virtual unit
            defaultUnit = {
                unit_id: product?.base_unit_id || null,
                unit_name: unit || 'Unit',
                unit_abbreviation: unit || 'Unit',
                conversion_to_base: 1,
                purchase_price: null,
                sale_price: salePrice,
                is_base_unit: true
            };
        }

        saleItems[productId] = {

            id: productId,

            name: productName,

            quantity: 1,

            product_unit_id: defaultUnit.id,  // Add ProductUnit ID

            unit_id: defaultUnit.unit_id,
            
            unit_name: defaultUnit.unit_name,
            
            unit_abbreviation: defaultUnit.unit_abbreviation,
            
            package_name: defaultUnit.package_name || null,  // Add package name
            
            conversion_to_base: defaultUnit.conversion_to_base,

            unit: defaultUnit.unit_abbreviation, // Legacy field

            price: parseFloat(defaultUnit.sale_price ?? salePrice) || 0,

            stock: stock,
            
            // Store full product data including available units
            product_units: productUnits,
            base_unit_abbreviation: product?.base_unit?.abbreviation || unit || 'Unit'

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

    

    tbody.innerHTML = items.map(item => {
        // Build unit selector dropdown
        let unitOptions = '';
        
        if (item.product_units && item.product_units.length > 0) {
            // Multi-unit product - show all active units
            unitOptions = item.product_units.map(pu => {
                const selected = pu.id === item.product_unit_id ? 'selected' : '';

                // A ProductUnit is the base when it has no package_name AND conversion = 1.
                // Do NOT rely on pu.is_base_unit — that field can be stale when
                // multiple packages share the same unit_id.
                const isBase = (!pu.package_name || pu.package_name === '')
                    && Math.abs(parseFloat(pu.conversion_to_base) - 1.0) < 0.0001;

                // Display name: package_name when set, otherwise unit_name
                const displayName   = pu.package_name || pu.unit_name;
                const conversionText = isBase
                    ? 'Base'
                    : `${pu.conversion_to_base} ${item.base_unit_abbreviation}`;

                return `<option value="${pu.id}" data-unit-id="${pu.unit_id}" data-package="${pu.package_name || ''}" ${selected}>${displayName} (${conversionText})</option>`;
            }).join('');
        } else {
            // Legacy product - show single option
            const displayName = item.display_name || item.unit_abbreviation || item.unit || 'Unit';
            unitOptions = `<option value="${item.unit_id || ''}" selected>${displayName}</option>`;
        }
        
        // Generate conversion preview text
        let conversionPreview = '';
        if (item.conversion_to_base && item.conversion_to_base !== 1) {
            const baseQty = (item.quantity * item.conversion_to_base).toFixed(2);
            conversionPreview = `<div class="text-muted" style="font-size: 0.7rem; margin-top: 2px;">
                ${item.quantity} × ${item.conversion_to_base} = ${baseQty} ${item.base_unit_abbreviation}
            </div>`;
        }
        
        // Calculate stock in the selected unit
        let displayStock = item.stock;  // Default to base unit stock
        if (item.conversion_to_base && item.conversion_to_base > 0) {
            displayStock = Math.floor(item.stock / item.conversion_to_base);
        }
        
        return `

        <tr>

            <td><strong>${item.name}</strong></td>

            <td class="text-center">
                <strong>${displayStock}</strong>
                ${item.conversion_to_base !== 1 ? `<br><small class="text-muted">(${item.stock} ${item.base_unit_abbreviation})</small>` : ''}
            </td>

            <td class="text-center">

                <input type="number" class="form-control form-control-sm text-center" style="width: 60px;" 

                       value="${item.quantity}" min="1" onchange="updateQty(${item.id}, this.value)">
                ${conversionPreview}

            </td>

            <td>

                <select class="form-select form-select-sm" 
                        onchange="updateSaleItemUnit(${item.id}, this.value)"
                        ${item.product_units && item.product_units.length > 1 ? '' : 'disabled'}>
                    ${unitOptions}
                </select>

            </td>

            <td>

                <input type="number" class="form-control form-control-sm text-end" style="width: 100px;" 

                       value="${item.price}" step="0.01" onchange="updatePrice(${item.id}, this.value)">

            </td>

            <td class="text-end"><strong>Rs. ${Math.round(item.quantity * item.price).toLocaleString('en-PK')}</strong></td>

            <td class="text-center">

                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSaleItem(${item.id})">

                    <i class="bi bi-trash"></i>

                </button>

            </td>

        </tr>

    `;
    }).join('');

    

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

/**
 * Handle unit change for a sale item
 * Updates conversion factor, prices, and re-renders to show new conversion preview
 */
function updateSaleItemUnit(productId, productUnitId) {
    const item = saleItems[productId];
    if (!item) return;
    
    const productUnitIdNum = parseInt(productUnitId);
    
    // Find the selected ProductUnit by its unique ID (not unit_id!)
    const selectedUnit = item.product_units?.find(pu => pu.id === productUnitIdNum);
    
    if (!selectedUnit) {
        console.error('Selected product unit not found:', productUnitId);
        return;
    }
    
    // Update item with new unit data
    item.product_unit_id = selectedUnit.id;  // Store the ProductUnit ID
    item.unit_id = selectedUnit.unit_id;
    item.unit_name = selectedUnit.unit_name;
    item.unit_abbreviation = selectedUnit.unit_abbreviation;
    item.package_name = selectedUnit.package_name;
    item.conversion_to_base = selectedUnit.conversion_to_base;
    item.unit = selectedUnit.unit_abbreviation; // Legacy compatibility
    
    // Update price with unit-specific sale price if available
    if (selectedUnit.sale_price !== null && selectedUnit.sale_price !== undefined) {
        item.price = parseFloat(selectedUnit.sale_price);
    }
    
    // Re-render the table to update conversion preview and prices
    renderSaleItems();
}

// Calculate total

function calculateTotal() {

    const subtotal = Object.values(saleItems).reduce((sum, item) => sum + (item.quantity * item.price), 0);

    const discount = parseFloat(document.getElementById('discount').value) || 0;

    const total = subtotal - discount;

    

    document.getElementById('subtotal').textContent = Math.round(subtotal).toLocaleString('en-PK');

    document.getElementById('summary_subtotal').textContent = Math.round(subtotal).toLocaleString('en-PK');

    document.getElementById('summary_discount').textContent = Math.round(discount).toLocaleString('en-PK');

    document.getElementById('total').textContent = Math.round(total).toLocaleString('en-PK');

    

    calculateRemaining();

}

// Integer Paid Amount Handler - Prevents decimals, strips non-numeric, prevents mouse wheel
function integerPaidAmountHandler(inputElement) {
    // Prevent mouse wheel scrolling
    inputElement.addEventListener('wheel', function(e) {
        e.preventDefault();
    });
    
    // On keypress, prevent decimal point from being entered
    inputElement.addEventListener('keypress', function(e) {
        // Allow only digits and minus sign
        if (!/[\d-]/.test(e.key)) {
            e.preventDefault();
        }
    });
    
    // On input, allow only digits and minus sign
    inputElement.addEventListener('input', function(e) {
        const cursorPos = this.selectionStart;
        this.value = this.value.replace(/[^\d-]/g, '');
        // Restore cursor position
        this.setSelectionRange(cursorPos, cursorPos);
    });
    
    // On blur, strip any decimal values and ensure integer
    inputElement.addEventListener('blur', function(e) {
        const value = this.value.replace(/[^\d-]/g, '');
        this.value = value || '0';
    });
    
    // Prevent keyboard-based increment/decrement (arrow keys in number input)
    inputElement.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
            e.preventDefault();
        }
    });
}

// Calculate remaining

function calculateRemaining() {

    const subtotal = Object.values(saleItems).reduce((sum, item) => sum + (item.quantity * item.price), 0);

    const discount = parseInt(document.getElementById('discount').value) || 0;

    const total = subtotal - discount;

    const paidAmount = parseInt(document.getElementById('paid_amount').value) || 0;

    const remaining = total - paidAmount;

    

    document.getElementById('remaining_udhar').value = remaining;

    

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

            document.getElementById('familySearch').value = family.name;

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

function focusSaleField(field) {
    if (!field) {
        return;
    }

    field.scrollIntoView({ behavior: 'smooth', block: 'center' });
    field.focus({ preventScroll: true });
}

document.getElementById('saleForm').addEventListener('submit', function(e) {

    e.preventDefault();

    

    const customerId = document.getElementById('customer_id').value.trim();

    const walkinName = document.getElementById('walkin_name').value.trim();

    const walkinPhone = document.getElementById('walkin_phone').value.trim();

    

    // Validation - need either a selected customer OR a walk-in name

    if (!customerId && !walkinName) {

        focusSaleField(document.getElementById('walkin_name'));

        return;

    }

    

    if (Object.keys(saleItems).length === 0) {

        focusSaleField(document.getElementById('productSearch'));

        return;
    }


    const invalidItem = Object.values(saleItems).find(item => {
        return !Number.isFinite(Number(item.quantity)) || Number(item.quantity) <= 0 ||
            !Number.isFinite(Number(item.price)) || Number(item.price) < 0;
    });

    if (invalidItem) {
        const itemRow = Array.from(document.querySelectorAll('#saleItemsTable tr'))
            .find(row => row.textContent.includes(invalidItem.name));
        const invalidInput = itemRow?.querySelector('input[type="number"]');
        focusSaleField(invalidInput || document.getElementById('productSearch'));
        return;
    }

    const outOfStockItem = Object.values(saleItems).find(item => {
        // Calculate available stock in the selected unit
        let availableStock = item.stock;
        if (item.conversion_to_base && item.conversion_to_base > 0) {
            availableStock = Math.floor(item.stock / item.conversion_to_base);
        }
        return Number(item.quantity) > Number(availableStock);
    });

    if (outOfStockItem) {
        const itemRow = Array.from(document.querySelectorAll('#saleItemsTable tr'))
            .find(row => row.textContent.includes(outOfStockItem.name));
        const quantityInput = itemRow?.querySelector('input[type="number"]');
        focusSaleField(quantityInput || document.getElementById('productSearch'));
        return;
    }

    

    const items = Object.values(saleItems).map(item => ({

        product_id:      item.id,

        quantity:        item.quantity,

        unit_price:      item.price,

        unit_id:         item.unit_id        || null,

        product_unit_id: item.product_unit_id || null

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
