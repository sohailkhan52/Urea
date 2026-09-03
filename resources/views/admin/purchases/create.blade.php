@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="page-title">Create New Purchase</h1>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Purchases
                </a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> Please fix the following errors:
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form id="purchaseForm" action="{{ route('admin.purchases.store') }}" method="POST" class="needs-validation" novalidate>
        @csrf
        
        <!-- Hidden action field for validation -->
        <input type="hidden" name="action" value="confirm">

        <div class="row">
            <!-- LEFT COLUMN: Supplier & Products -->
            <div class="col-lg-8">
                <!-- SUPPLIER SECTION -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-building"></i> Select Supplier
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="form-group mb-0">
                                    <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                                    <input type="hidden" id="supplier_id" name="supplier_id" value="{{ old('supplier_id') }}" required>
                                    
                                    <div class="input-group">
                                        <input type="text" 
                                               id="supplierSearch" 
                                               class="form-control" 
                                               placeholder="Search supplier by name, company, or phone..."
                                               autocomplete="off">
                                        <button class="btn btn-outline-secondary" type="button" id="clearSupplier">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>

                                    <!-- Recent Used Suppliers -->
                                    <div id="recentSuppliers" class="mt-2" style="display: none;">
                                        <small class="text-muted">Recently Used:</small>
                                        <div class="d-flex flex-wrap gap-2 mt-1" id="recentSuppliersList"></div>
                                    </div>

                                    <!-- Supplier dropdown list -->
                                    <div id="supplierDropdown" class="mt-2" style="display: none; max-height: 400px; overflow-y: auto;">
                                        <div class="row g-2" id="supplierGrid"></div>
                                    </div>

                                    <!-- Selected supplier info -->
                                    <div id="supplierInfo" class="alert alert-info mt-3" style="display: none;">
                                        <div><strong>Selected:</strong> <span id="selectedSupplierName"></span></div>
                                        <div><small id="selectedSupplierDetails"></small></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary w-100 mt-4" data-bs-toggle="modal" data-bs-target="#newSupplierModal">
                                    <i class="bi bi-plus-lg"></i> New Supplier
                                </button>
                            </div>
                        </div>

                        <input type="hidden" id="warehouse_id" name="warehouse_id" value="{{ $defaultWarehouse->id }}")>
                        <input type="hidden" id="purchase_date" name="purchase_date" value="{{ \Carbon\Carbon::today()->toDateString() }}">
                    </div>
                </div>

                <!-- PRODUCT SEARCH SECTION -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-search"></i> Search & Add Products
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="form-group mb-0">
                                    <label for="productSearch" class="form-label">Search Product</label>
                                    <input type="text" 
                                           id="productSearch" 
                                           class="form-control" 
                                           placeholder="Search by name, SKU, or barcode..."
                                           autocomplete="off">
                                    
                                    <!-- Recent Used Products -->
                                    <div id="recentProducts" class="mt-2" style="display: none;">
                                        <small class="text-muted">Recently Used:</small>
                                        <div class="d-flex flex-wrap gap-2 mt-1" id="recentProductsList"></div>
                                    </div>

                                    <!-- Product dropdown grid -->
                                    <div id="productDropdown" class="mt-2" style="display: none; max-height: 400px; overflow-y: auto;">
                                        <div class="row g-2" id="productGrid"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-primary w-100 mt-4" data-bs-toggle="modal" data-bs-target="#newProductModal">
                                    <i class="bi bi-plus-lg"></i> New Product
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PURCHASE ITEMS SECTION -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul"></i> Purchase Items
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th style="width: 80px;">Qty</th>
                                        <th style="width: 100px;">Unit</th>
                                        <th style="width: 100px;">Cost Price</th>
                                        <th style="width: 100px;">Sell Price</th>
                                        <th style="width: 100px;">Total</th>
                                        <th style="width: 60px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <tr id="emptyRow" class="text-center text-muted">
                                        <td colspan="7" class="py-3">
                                            <i class="bi bi-inbox"></i> No items added yet. Search and select products above.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Hidden input to store items as JSON -->
                        <input type="hidden" id="items" name="items" value="[]">
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Summary & Payment -->
            <div class="col-lg-4">
                <!-- CALCULATIONS SECTION -->
                <div class="card mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-calculator"></i> Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label text-muted small">Subtotal</label>
                                <div class="h5 mb-0">Rs. <span id="subtotal">0.00</span></div>
                            </div>
                            <div class="col-6 text-end">
                                <label class="form-label text-muted small">Items</label>
                                <div class="h5 mb-0"><span id="itemCount">0</span></div>
                            </div>
                        </div>

                        <hr>

                        <!-- Discount Section -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <label for="discountType" class="form-label">Discount Type</label>
                                <select id="discountType" class="form-select form-select-sm" onchange="updateDiscount()">
                                    <option value="amount">Amount (Rs.)</option>
                                    <option value="percentage">Percentage (%)</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="discount" class="form-label">Discount</label>
                                <input type="number" 
                                       id="discount" 
                                       name="discount" 
                                       class="form-control form-control-sm" 
                                       placeholder="0"
                                       min="0" 
                                       step="0.01"
                                       oninput="updateDiscount()">
                            </div>
                        </div>

                        <hr>

                        <!-- Transport & Other Costs -->
                        <div class="mb-3">
                            <label for="transport_cost" class="form-label">Transport Cost (Rs.)</label>
                            <input type="number" 
                                   id="transport_cost" 
                                   name="transport_cost" 
                                   class="form-control form-control-sm" 
                                   placeholder="0"
                                   min="0" 
                                   step="0.01"
                                   oninput="updateCalculations()">
                        </div>

                        <div class="mb-3">
                            <label for="other_expenses" class="form-label">Other Expenses (Rs.)</label>
                            <input type="number" 
                                   id="other_expenses" 
                                   name="other_expenses" 
                                   class="form-control form-control-sm" 
                                   placeholder="0"
                                   min="0" 
                                   step="0.01"
                                   oninput="updateCalculations()">
                        </div>

                        <hr>

                        <!-- Total Payment -->
                        <div class="mb-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal:</span>
                                <strong>Rs. <span id="display_subtotal">0.00</span></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">- Discount:</span>
                                <strong class="text-danger">Rs. <span id="display_discount">0.00</span></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">+ Transport:</span>
                                <strong>Rs. <span id="display_transport">0.00</span></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                                <span class="text-muted">+ Other:</span>
                                <strong>Rs. <span id="display_other">0.00</span></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <strong>Total Payment:</strong>
                                <strong class="h5 text-success">Rs. <span id="total_amount">0.00</span></strong>
                            </div>
                        </div>

                        <hr>

                        <!-- PAYMENT SECTION -->
                        <div class="mb-3">
                            <label for="paid_amount" class="form-label">Paid Amount (Rs.)</label>
                            <input type="number" 
                                   id="paid_amount" 
                                   name="paid_amount" 
                                   class="form-control" 
                                   placeholder="0"
                                   min="0" 
                                   max="999999.99"
                                   step="0.01"
                                   oninput="updatePaymentStatus()">
                        </div>

                        <!-- Remaining Payable -->
                        <div class="mb-3 p-3 bg-warning bg-opacity-10 rounded">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Remaining Payable:</span>
                                <strong class="text-warning">Rs. <span id="remaining_payable">0.00</span></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Payment Status:</span>
                                <span id="paymentStatus" class="badge bg-secondary">Not Started</span>
                            </div>
                        </div>

                        <hr>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea id="notes" 
                                      name="notes" 
                                      class="form-control form-control-sm" 
                                      rows="3" 
                                      placeholder="Add any notes about this purchase..."></textarea>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg" id="submitBtn" disabled>
                                <i class="bi bi-check-circle"></i> Save & Confirm Purchase
                            </button>
                            <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-lg"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- NEW SUPPLIER MODAL -->
<div class="modal fade" id="newSupplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newSupplierForm">
                    <div class="mb-3">
                        <label for="supplier_name" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="supplier_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="supplier_company" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="supplier_company" name="company_name">
                    </div>
                    <div class="mb-3">
                        <label for="supplier_phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="supplier_phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="supplier_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="supplier_email" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="supplier_address" class="form-label">Address</label>
                        <textarea class="form-control" id="supplier_address" name="address" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="supplier_city" class="form-label">City</label>
                        <input type="text" class="form-control" id="supplier_city" name="city">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveSupplierBtn">Save Supplier</button>
            </div>
        </div>
    </div>
</div>

<!-- NEW PRODUCT MODAL -->
<div class="modal fade" id="newProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newProductForm">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="product_name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="product_name" name="name" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="product_unit" class="form-label">Unit <span class="text-danger">*</span></label>
                                <select class="form-select" id="product_unit" name="unit" required>
                                    <option value="">-- Select Unit --</option>
                                    <option value="KG">Kilogram (KG)</option>
                                    <option value="MG">Milligram (MG)</option>
                                    <option value="Piece">Piece</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="product_purchase_price" class="form-label">Purchase Price (Rs.) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="product_purchase_price" name="purchase_price" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="product_sale_price" class="form-label">Sale Price (Rs.) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="product_sale_price" name="sale_price" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveProductBtn">Save Product</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // ========== DATA STORAGE ==========
    let purchaseItems = [];
    let allProducts = [];
    let allSuppliers = [];
    let currentSupplier = null;

    // ========== INITIALIZATION ==========
    document.addEventListener('DOMContentLoaded', function() {
        // Display recent items immediately (from localStorage)
        displayRecentSuppliers();
        displayRecentProducts();
        // Then load fresh data from server
        loadSuppliers();
        loadProducts();
        setupEventListeners();
    });

    // ========== LOAD DATA FROM SERVER ==========
    function loadSuppliers() {
        fetch('{{ route("admin.suppliers.getAll") }}')
            .then(response => response.json())
            .then(data => {
                allSuppliers = data;
                
                // Update recent suppliers in localStorage - remove deleted ones and update data
                let recentSuppliers = JSON.parse(localStorage.getItem('recentSuppliers') || '[]');
                if (recentSuppliers.length > 0) {
                    // Filter out deleted suppliers and update with fresh data
                    recentSuppliers = recentSuppliers
                        .filter(recent => data.some(supplier => supplier.id === recent.id))
                        .map(recent => {
                            const fresh = data.find(s => s.id === recent.id);
                            return fresh || recent;
                        });
                    localStorage.setItem('recentSuppliers', JSON.stringify(recentSuppliers));
                }
                
                // Initialize localStorage with server data if empty
                if (recentSuppliers.length === 0 && data.length > 0) {
                    // Take first 10 from server as initial recent
                    let recent = data.slice(0, Math.min(10, data.length));
                    localStorage.setItem('recentSuppliers', JSON.stringify(recent));
                }
                
                displayRecentSuppliers();
            })
            .catch(error => console.error('Error loading suppliers:', error));
    }

    function loadProducts() {
        fetch('{{ route("admin.products.getAll") }}')
            .then(response => response.json())
            .then(data => {
                allProducts = data;
                
                // Update recent products in localStorage - remove deleted ones and update with fresh prices
                let recentProducts = JSON.parse(localStorage.getItem('recentProducts') || '[]');
                if (recentProducts.length > 0) {
                    // Filter out deleted products and update with fresh data
                    recentProducts = recentProducts
                        .filter(recent => data.some(product => product.id === recent.id))
                        .map(recent => {
                            const fresh = data.find(p => p.id === recent.id);
                            return fresh || recent; // Use fresh data if available
                        });
                    localStorage.setItem('recentProducts', JSON.stringify(recentProducts));
                }
                
                // Initialize localStorage with server data if empty
                if (recentProducts.length === 0 && data.length > 0) {
                    // Take first 10 from server as initial recent
                    let recent = data.slice(0, Math.min(10, data.length));
                    localStorage.setItem('recentProducts', JSON.stringify(recent));
                }
                
                displayRecentProducts();
            })
            .catch(error => console.error('Error loading products:', error));
    }

    // ========== RECENT USED TRACKING ==========
    function addToRecentSuppliers(supplier) {
        let recent = JSON.parse(localStorage.getItem('recentSuppliers') || '[]');
        // Remove if exists to avoid duplicates
        recent = recent.filter(s => s.id !== supplier.id);
        // Add to beginning
        recent.unshift(supplier);
        // Keep only last 10
        recent = recent.slice(0, 10);
        localStorage.setItem('recentSuppliers', JSON.stringify(recent));
        displayRecentSuppliers();
    }

    function addToRecentProducts(product) {
        // Always use fresh product data from allProducts array to get latest prices
        const freshProduct = allProducts.find(p => p.id === product.id) || product;
        
        let recent = JSON.parse(localStorage.getItem('recentProducts') || '[]');
        // Remove if exists to avoid duplicates
        recent = recent.filter(p => p.id !== freshProduct.id);
        // Add fresh data to beginning
        recent.unshift(freshProduct);
        // Keep only last 10
        recent = recent.slice(0, 10);
        localStorage.setItem('recentProducts', JSON.stringify(recent));
        displayRecentProducts();
    }

    function displayRecentSuppliers() {
        const recentContainer = document.getElementById('recentSuppliers');
        const recentList = document.getElementById('recentSuppliersList');
        
        let recent = JSON.parse(localStorage.getItem('recentSuppliers') || '[]');
        
        // Filter out deleted suppliers - only show those that exist in allSuppliers
        const validRecent = recent.filter(recentSupplier => 
            allSuppliers.some(supplier => supplier.id === recentSupplier.id)
        );
        
        // Update localStorage to remove deleted suppliers
        if (validRecent.length !== recent.length) {
            localStorage.setItem('recentSuppliers', JSON.stringify(validRecent));
        }
        
        // Deduplicate by name - keep first occurrence only
        const seenNames = new Set();
        const uniqueRecent = [];
        for (const supplier of validRecent) {
            if (!seenNames.has(supplier.name)) {
                seenNames.add(supplier.name);
                uniqueRecent.push(supplier);
            }
        }
        
        if (uniqueRecent.length === 0) {
            recentContainer.style.display = 'none';
            return;
        }

        recentContainer.style.display = 'block';
        recentList.innerHTML = '';

        uniqueRecent.forEach(supplier => {
            const badge = document.createElement('span');
            badge.className = 'badge bg-light text-dark border cursor-pointer';
            badge.style.cursor = 'pointer';
            badge.textContent = supplier.name;
            badge.addEventListener('click', function() {
                selectSupplier(supplier);
            });
            recentList.appendChild(badge);
        });
    }

    function displayRecentProducts() {
        const recentContainer = document.getElementById('recentProducts');
        const recentList = document.getElementById('recentProductsList');
        
        let recent = JSON.parse(localStorage.getItem('recentProducts') || '[]');
        
        // Filter out deleted products - only show those that exist in allProducts
        const validRecent = recent.filter(recentProduct => 
            allProducts.some(product => product.id === recentProduct.id)
        );
        
        // Update localStorage to remove deleted products
        if (validRecent.length !== recent.length) {
            localStorage.setItem('recentProducts', JSON.stringify(validRecent));
        }
        
        // Deduplicate by name - keep first occurrence only
        const seenNames = new Set();
        const uniqueRecent = [];
        for (const product of validRecent) {
            if (!seenNames.has(product.name)) {
                seenNames.add(product.name);
                uniqueRecent.push(product);
            }
        }
        
        if (uniqueRecent.length === 0) {
            recentContainer.style.display = 'none';
            return;
        }

        recentContainer.style.display = 'block';
        recentList.innerHTML = '';

        uniqueRecent.forEach(product => {
            const badge = document.createElement('span');
            badge.className = 'badge bg-light text-dark border cursor-pointer';
            badge.style.cursor = 'pointer';
            badge.textContent = product.name;
            badge.addEventListener('click', function() {
                // Fetch fresh product data from server to get latest prices
                const freshProduct = allProducts.find(p => p.id === product.id);
                if (freshProduct) {
                    addProductToItems(freshProduct);
                } else {
                    // Fallback to cached data if not found in allProducts
                    addProductToItems(product);
                }
            });
            recentList.appendChild(badge);
        });
    }

    // ========== SUPPLIER SEARCH & SELECTION ==========
    function setupEventListeners() {
        // Supplier Search
        const supplierSearch = document.getElementById('supplierSearch');
        const supplierDropdown = document.getElementById('supplierDropdown');
        const clearSupplierBtn = document.getElementById('clearSupplier');

        // Show all suppliers on focus/click
        supplierSearch.addEventListener('focus', function() {
            displaySupplierResults(allSuppliers);
            // Hide recent items when showing full dropdown
            document.getElementById('recentSuppliers').style.display = 'none';
            supplierDropdown.style.display = allSuppliers.length > 0 ? 'block' : 'none';
        });

        // Filter suppliers on input
        supplierSearch.addEventListener('input', function() {
            const term = this.value.trim();
            
            if (term.length === 0) {
                // If empty, show all suppliers
                displaySupplierResults(allSuppliers);
                // Show recent items again when clearing search
                displayRecentSuppliers();
                supplierDropdown.style.display = allSuppliers.length > 0 ? 'block' : 'none';
                return;
            }

            const filtered = allSuppliers.filter(s =>
                s.name.toLowerCase().includes(term.toLowerCase()) ||
                (s.company_name && s.company_name.toLowerCase().includes(term.toLowerCase())) ||
                (s.phone && s.phone.includes(term))
            );

            displaySupplierResults(filtered);
            // Hide recent items when filtering
            document.getElementById('recentSuppliers').style.display = 'none';
            supplierDropdown.style.display = filtered.length > 0 ? 'block' : 'none';
        });

        clearSupplierBtn.addEventListener('click', function() {
            supplierSearch.value = '';
            supplierDropdown.style.display = 'none';
            document.getElementById('supplier_id').value = '';
            document.getElementById('supplierInfo').style.display = 'none';
            currentSupplier = null;
            checkFormValidity();
        });

        // Product Search
        const productSearch = document.getElementById('productSearch');
        const productDropdown = document.getElementById('productDropdown');

        // Show all products on focus/click
        productSearch.addEventListener('focus', function() {
            displayProductResults(allProducts);
            // Hide recent items when showing full dropdown
            document.getElementById('recentProducts').style.display = 'none';
            productDropdown.style.display = allProducts.length > 0 ? 'block' : 'none';
        });

        // Filter products on input
        productSearch.addEventListener('input', function() {
            const term = this.value.trim();

            if (term.length === 0) {
                // If empty, show all products
                displayProductResults(allProducts);
                // Show recent items again when clearing search
                displayRecentProducts();
                productDropdown.style.display = allProducts.length > 0 ? 'block' : 'none';
                return;
            }

            const filtered = allProducts.filter(p =>
                p.name.toLowerCase().includes(term.toLowerCase()) ||
                (p.sku && p.sku.toLowerCase().includes(term.toLowerCase())) ||
                (p.barcode && p.barcode.includes(term))
            );

            displayProductResults(filtered);
            // Hide recent items when filtering
            document.getElementById('recentProducts').style.display = 'none';
            productDropdown.style.display = filtered.length > 0 ? 'block' : 'none';
        });

        // Close dropdowns on outside click
        document.addEventListener('click', function(event) {
            if (!event.target.closest('#supplierSearch') && !event.target.closest('#supplierDropdown')) {
                supplierDropdown.style.display = 'none';
            }
            if (!event.target.closest('#productSearch') && !event.target.closest('#productDropdown')) {
                productDropdown.style.display = 'none';
            }
        });

        // New Supplier Modal
        document.getElementById('saveSupplierBtn').addEventListener('click', saveNewSupplier);

        // New Product Modal
        const saveProductBtn = document.getElementById('saveProductBtn');
        if (saveProductBtn) {
            console.log('Save Product button found, attaching event listener');
            saveProductBtn.addEventListener('click', function(e) {
                console.log('Save Product button clicked!', e);
                saveNewProduct();
            });
        } else {
            console.error('Save Product button NOT FOUND!');
        }
    }

    // ========== SUPPLIER FUNCTIONS ==========
    function displaySupplierResults(suppliers) {
        const grid = document.getElementById('supplierGrid');
        grid.innerHTML = '';

        // Limit to 15 suppliers (most recent)
        const limited = suppliers.slice(0, 15);

        limited.forEach(supplier => {
            const col = document.createElement('div');
            col.className = 'col-lg-2 col-md-3 col-sm-4 col-6'; // 5 items per row on large screens
            col.innerHTML = `
                <div class="card h-100 cursor-pointer supplier-card" style="cursor: pointer; border: 1px solid #ddd; transition: all 0.2s;">
                    <div class="card-body p-3">
                        <div class="text-center">
                            <div class="fw-bold small mb-1" style="word-break: break-word;">
                                ${supplier.name}
                            </div>
                            ${supplier.company_name ? `<small class="text-muted d-block" style="font-size: 11px;">${supplier.company_name}</small>` : ''}
                            ${supplier.phone ? `<small class="text-primary d-block" style="font-size: 11px;">${supplier.phone}</small>` : ''}
                        </div>
                    </div>
                </div>
            `;
            
            col.addEventListener('click', function(e) {
                e.preventDefault();
                selectSupplier(supplier);
            });
            
            col.addEventListener('mouseover', function() {
                this.querySelector('.supplier-card').style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
                this.querySelector('.supplier-card').style.borderColor = '#007bff';
            });
            
            col.addEventListener('mouseout', function() {
                this.querySelector('.supplier-card').style.boxShadow = 'none';
                this.querySelector('.supplier-card').style.borderColor = '#ddd';
            });
            
            grid.appendChild(col);
        });
    }

    function selectSupplier(supplier) {
        currentSupplier = supplier;
        
        // Validate supplier object
        if (!supplier || !supplier.id) {
            console.error('Invalid supplier object:', supplier);
            showAlert('danger', 'Invalid supplier data');
            return;
        }

        const supplierIdField = document.getElementById('supplier_id');
        const supplierSearchField = document.getElementById('supplierSearch');
        const supplierDropdown = document.getElementById('supplierDropdown');
        const supplierInfo = document.getElementById('supplierInfo');
        const selectedSupplierName = document.getElementById('selectedSupplierName');
        const selectedSupplierDetails = document.getElementById('selectedSupplierDetails');

        // Safety checks for all elements
        if (supplierIdField) supplierIdField.value = supplier.id;
        if (supplierSearchField) supplierSearchField.value = supplier.name;
        if (supplierDropdown) supplierDropdown.style.display = 'none';
        
        if (selectedSupplierName) {
            selectedSupplierName.textContent = supplier.name || '';
        }
        
        if (selectedSupplierDetails) {
            selectedSupplierDetails.innerHTML = `
                ${supplier.company_name ? `<strong>${supplier.company_name}</strong> | ` : ''}
                ${supplier.phone ? `Phone: ${supplier.phone}` : ''}
            `;
        }
        
        if (supplierInfo) {
            supplierInfo.style.display = 'block';
        }

        // Track this supplier as recently used
        addToRecentSuppliers(supplier);
        
        checkFormValidity();
    }

    function saveNewSupplier() {
        const form = document.getElementById('newSupplierForm');
        
        // Validate form
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        const formData = new FormData(form);

        fetch('{{ route("admin.suppliers.storeAjax") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || 'Failed to create supplier');
                });
            }
            return response.json();
        })
        .then(data => {
            if (!data.id || !data.name) {
                throw new Error('Invalid supplier data received');
            }
            allSuppliers.push(data);
            selectSupplier(data);
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('newSupplierModal'));
            if (modal) modal.hide();
            
            form.reset();
            form.classList.remove('was-validated');
            showAlert('success', data.message || 'Supplier created successfully.');
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Error creating supplier: ' + error.message);
        });
    }

    // ========== PRODUCT FUNCTIONS ==========
    function displayProductResults(products) {
        const grid = document.getElementById('productGrid');
        grid.innerHTML = '';

        // Limit to 15 products (most recent)
        const limited = products.slice(0, 15);

        limited.forEach(product => {
            const col = document.createElement('div');
            col.className = 'col-lg-2 col-md-3 col-sm-4 col-6'; // 5 items per row on large screens
            col.innerHTML = `
                <div class="card h-100 cursor-pointer product-card" style="cursor: pointer; border: 1px solid #ddd; transition: all 0.2s;">
                    <div class="card-body p-3">
                        <div class="text-center">
                            <div class="fw-bold small mb-1" style="word-break: break-word;">
                                ${product.name}
                            </div>
                            <small class="text-success d-block" style="font-size: 11px;">
                                Rs. ${parseFloat(product.purchase_price).toFixed(0)}
                            </small>
                        </div>
                    </div>
                </div>
            `;
            
            col.addEventListener('click', function(e) {
                e.preventDefault();
                addProductToItems(product);
            });
            
            col.addEventListener('mouseover', function() {
                this.querySelector('.product-card').style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
                this.querySelector('.product-card').style.borderColor = '#007bff';
            });
            
            col.addEventListener('mouseout', function() {
                this.querySelector('.product-card').style.boxShadow = 'none';
                this.querySelector('.product-card').style.borderColor = '#ddd';
            });
            
            grid.appendChild(col);
        });
    }

    function addProductToItems(product) {
        // Check if product already exists
        const existingItem = purchaseItems.find(item => item.product_id === product.id);
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            purchaseItems.push({
                product_id: product.id,
                product_name: product.name,
                quantity: 1,
                unit_price: parseFloat(product.purchase_price),
                sale_price: parseFloat(product.sale_price),
                unit: product.unit
            });
        }

        // Track this product as recently used
        addToRecentProducts(product);

        document.getElementById('productSearch').value = '';
        document.getElementById('productDropdown').style.display = 'none';
        renderItemsTable();
        updateCalculations();
    }

    function saveNewProduct() {
        console.log('saveNewProduct called!'); // Debug: Function called
        
        const form = document.getElementById('newProductForm');
        
        if (!form) {
            console.error('Form not found!');
            showAlert('danger', 'Form not found. Please refresh the page.');
            return;
        }
        
        console.log('Form found:', form); // Debug: Form element
        
        // Client-side validation
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            showAlert('warning', 'Please fill in all required fields');
            console.log('Form validation failed');
            return;
        }

        console.log('Form validation passed'); // Debug: Validation passed

        const formData = new FormData(form);

        // Debug logging
        console.log('Sending product data:', {
            name: formData.get('name'),
            unit: formData.get('unit'),
            purchase_price: formData.get('purchase_price'),
            sale_price: formData.get('sale_price')
        });

        fetch('{{ route("admin.products.storeAjax") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            
            if (response.status === 422) {
                // Validation error - extract error messages
                return response.json().then(errors => {
                    console.error('Validation errors:', errors);
                    const errorMessages = Object.values(errors.errors || errors).flat();
                    throw new Error(errorMessages.join(', '));
                });
            }
            
            if (!response.ok) {
                // Try to get error message from response
                return response.json().then(errorData => {
                    console.error('Server error data:', errorData);
                    throw new Error(errorData.message || errorData.error || 'Server error: ' + response.statusText);
                }).catch(jsonError => {
                    // If JSON parsing fails, just use status text
                    console.error('Failed to parse error JSON:', jsonError);
                    throw new Error('Server error (' + response.status + '): ' + response.statusText);
                });
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Product created:', data);
            
            allProducts.push({
                id: data.id,
                name: data.name,
                unit: data.unit,
                purchase_price: data.purchase_price,
                sale_price: data.sale_price
            });
            
            addProductToItems(data);
            bootstrap.Modal.getInstance(document.getElementById('newProductModal')).hide();
            form.reset();
            form.classList.remove('was-validated');
            showAlert('success', 'Product created and added to purchase items');
        })
        .catch(error => {
            console.error('Error creating product:', error);
            showAlert('danger', 'Error creating product: ' + error.message);
        });
    }

    // ========== ITEMS TABLE RENDERING ==========
    function renderItemsTable() {
        const tbody = document.getElementById('itemsBody');
        const emptyRow = document.getElementById('emptyRow');

        // Store the currently focused element and cursor position
        const activeElement = document.activeElement;
        let activeIndex = -1;
        let activeField = null;
        let cursorPosition = 0;

        // Check if the active element is one of our input fields
        if (activeElement && activeElement.tagName === 'INPUT' && activeElement.type === 'number') {
            // Find which row and field is active
            const row = activeElement.closest('tr');
            if (row) {
                activeIndex = Array.from(tbody.children).indexOf(row);
                // Determine which field (quantity, unit_price, or sale_price)
                if (activeElement.getAttribute('data-field')) {
                    activeField = activeElement.getAttribute('data-field');
                }
                cursorPosition = activeElement.selectionStart;
            }
        }

        if (purchaseItems.length === 0) {
            tbody.innerHTML = '<tr id="emptyRow" class="text-center text-muted"><td colspan="7" class="py-3"><i class="bi bi-inbox"></i> No items added yet. Search and select products above.</td></tr>';
            return;
        }

        tbody.innerHTML = purchaseItems.map((item, index) => `
            <tr>
                <td>
                    <strong>${item.product_name}</strong>
                </td>
                <td>
                    <input type="number" 
                           class="form-control form-control-sm" 
                           value="${item.quantity}" 
                           min="0.01" 
                           step="0.01"
                           data-field="quantity"
                           data-index="${index}"
                           oninput="updateItemQuantity(${index}, this.value)"
                           onblur="updateItemQuantity(${index}, this.value)">
                </td>
                <td>
                    <small class="text-muted">${item.unit || 'KG'}</small>
                </td>
                <td>
                    <input type="number" 
                           class="form-control form-control-sm" 
                           value="${item.unit_price.toFixed(2)}" 
                           min="0" 
                           step="0.01"
                           data-field="unit_price"
                           data-index="${index}"
                           oninput="updateItemPrice(${index}, this.value)"
                           onblur="updateItemPrice(${index}, this.value)">
                </td>
                <td>
                    <input type="number" 
                           class="form-control form-control-sm" 
                           value="${item.sale_price.toFixed(2)}" 
                           min="0" 
                           step="0.01"
                           data-field="sale_price"
                           data-index="${index}"
                           oninput="updateItemSalePrice(${index}, this.value)"
                           onblur="updateItemSalePrice(${index}, this.value)">
                </td>
                <td>
                    <strong>Rs. ${(item.quantity * item.unit_price).toFixed(2)}</strong>
                </td>
                <td>
                    <button type="button" 
                            class="btn btn-sm btn-danger rounded-circle p-2" 
                            onclick="removeItem(${index})"
                            title="Remove item"
                            style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </td>
            </tr>
        `).join('');

        // Restore focus and cursor position if there was an active element
        if (activeIndex >= 0 && activeField) {
            const newRow = tbody.children[activeIndex];
            if (newRow) {
                const input = newRow.querySelector(`input[data-field="${activeField}"]`);
                if (input) {
                    input.focus();
                    input.setSelectionRange(cursorPosition, cursorPosition);
                }
            }
        }
    }

    function updateItemQuantity(index, value) {
        purchaseItems[index].quantity = parseFloat(value) || 0;
        // Update the row total display
        updateRowTotal(index);
        // Only update calculations, don't re-render the table to preserve cursor
        updateCalculationsOnly();
    }

    function updateItemPrice(index, value) {
        purchaseItems[index].unit_price = parseFloat(value) || 0;
        // Update the row total display
        updateRowTotal(index);
        // Only update calculations, don't re-render the table to preserve cursor
        updateCalculationsOnly();
    }

    function updateItemSalePrice(index, value) {
        purchaseItems[index].sale_price = parseFloat(value) || 0;
        // No need to update row total as sale price doesn't affect purchase total
    }

    // Update the total display for a specific row without re-rendering
    function updateRowTotal(index) {
        const tbody = document.getElementById('itemsBody');
        const row = tbody.children[index];
        if (row) {
            const item = purchaseItems[index];
            const totalCell = row.cells[5]; // 6th column (0-indexed) is the Total column
            if (totalCell) {
                totalCell.innerHTML = `<strong>Rs. ${(item.quantity * item.unit_price).toFixed(2)}</strong>`;
            }
        }
    }

    function removeItem(index) {
        purchaseItems.splice(index, 1);
        renderItemsTable();
        updateCalculations();
    }

    // ========== CALCULATIONS ==========
    // Update only totals without re-rendering the table
    function updateCalculationsOnly() {
        const subtotal = purchaseItems.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
        
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('itemCount').textContent = purchaseItems.length;
        
        updateDiscount();
    }

    // Full update with table re-render
    function updateCalculations() {
        const subtotal = purchaseItems.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
        const transportCost = parseFloat(document.getElementById('transport_cost').value) || 0;
        const otherExpenses = parseFloat(document.getElementById('other_expenses').value) || 0;

        let discount = parseFloat(document.getElementById('discount').value) || 0;
        const discountType = document.getElementById('discountType').value;

        // If percentage, calculate actual discount amount
        if (discountType === 'percentage') {
            discount = (subtotal * discount) / 100;
        }

        const totalAmount = subtotal - discount + transportCost + otherExpenses;

        // Update display
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('display_subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('display_discount').textContent = discount.toFixed(2);
        document.getElementById('display_transport').textContent = transportCost.toFixed(2);
        document.getElementById('display_other').textContent = otherExpenses.toFixed(2);
        document.getElementById('total_amount').textContent = totalAmount.toFixed(2);
        document.getElementById('itemCount').textContent = purchaseItems.length;

        updatePaymentStatus();
    }

    function updateDiscount() {
        updateCalculations();
    }

    function updatePaymentStatus() {
        const totalAmount = parseFloat(document.getElementById('total_amount').textContent) || 0;
        const paidAmount = parseFloat(document.getElementById('paid_amount').value) || 0;
        const remainingPayable = Math.max(0, totalAmount - paidAmount);

        document.getElementById('remaining_payable').textContent = remainingPayable.toFixed(2);

        let status = 'Not Started';
        let statusBadge = 'secondary';

        if (paidAmount === 0) {
            status = 'Unpaid';
            statusBadge = 'danger';
        } else if (paidAmount >= totalAmount) {
            status = 'Paid';
            statusBadge = 'success';
        } else if (paidAmount > 0) {
            status = 'Partial';
            statusBadge = 'warning';
        }

        document.getElementById('paymentStatus').textContent = status;
        document.getElementById('paymentStatus').className = `badge bg-${statusBadge}`;
    }

    // ========== FORM SUBMISSION ==========
    function checkFormValidity() {
        const supplierId = document.getElementById('supplier_id').value;
        const hasItems = purchaseItems.length > 0;
        const submitBtn = document.getElementById('submitBtn');

        submitBtn.disabled = !supplierId || !hasItems;
    }

    document.getElementById('purchaseForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const supplierId = document.getElementById('supplier_id').value;
        if (!supplierId) {
            showAlert('danger', 'Please select a supplier');
            return;
        }

        if (purchaseItems.length === 0) {
            showAlert('danger', 'Please add at least one product');
            return;
        }

        // Debug: Log items before submission
        console.log('Purchase Items Before Submission:', purchaseItems);
        console.log('Purchase Items JSON:', JSON.stringify(purchaseItems));

        // Store items as JSON
        document.getElementById('items').value = JSON.stringify(purchaseItems);

        // Submit the form
        this.submit();
    });

    // Watch for item changes to enable/disable submit button
    const observer = new MutationObserver(() => checkFormValidity());
    observer.observe(document.getElementById('itemsTable'), { childList: true, subtree: true });

    // ========== UTILITY FUNCTIONS ==========
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.querySelector('.page-header').insertAdjacentHTML('afterend', alertHtml);
        setTimeout(() => {
            document.querySelector('.alert')?.remove();
        }, 5000);
    }

    // Initialize calculations on page load
    updateCalculations();
    checkFormValidity();
</script>
@endpush

@push('styles')
<style>
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .list-group-item {
        cursor: pointer;
    }

    .list-group-item:hover {
        background-color: #f8f9fa;
    }

    .table-responsive {
        max-height: 500px;
        overflow-y: auto;
    }

    .form-control-sm:focus,
    .form-select-sm:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    #emptyRow td {
        padding: 3rem 1rem;
    }
</style>
@endpush
@endsection
