@extends('layouts.admin')

@push('styles')
<style>
    @media (max-width: 575.98px) {
        .purchase-mobile-search::placeholder { font-size: 0.85em; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="page-title">Create New Purchase</h1>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary d-none d-sm-inline-block">
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
                <div class="card mb-4 supplier-autocomplete-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-building"></i> Select Supplier
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-none d-md-flex justify-content-end mb-2">
                            <button type="button" class="btn btn-primary purchase-search-action supplier-new-action" data-bs-toggle="modal" data-bs-target="#newSupplierModal">
                                <i class="bi bi-plus-lg"></i> <span>New Supplier</span>
                            </button>
                        </div>
                        <div class="row supplier-search-row">
                            <div class="col-12 supplier-search-column">
                                <div class="form-group mb-0">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label for="supplier_id" class="form-label mb-0">Supplier <span class="text-danger">*</span></label>
                                        <button type="button" class="btn btn-primary purchase-search-action supplier-new-action d-md-none" data-bs-toggle="modal" data-bs-target="#newSupplierModal">
                                            <i class="bi bi-plus-lg"></i> <span>New Supplier</span>
                                        </button>
                                    </div>
                                    <input type="hidden" id="supplier_id" name="supplier_id" value="{{ old('supplier_id') }}" required>
                                    
                                    <div class="purchase-search-wrapper" id="supplierSearchWrapper">
                                        <div class="input-group">
                                            <input type="text" 
                                                   id="supplierSearch" 
                                                   class="form-control purchase-mobile-search"
                                                   data-mobile-placeholder="Search supplier by..."
                                                   placeholder="Search supplier by name, company, or phone..."
                                                   autocomplete="off">
                                            <button class="btn btn-outline-secondary" type="button" id="clearSupplier">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>

                                        <!-- Supplier dropdown list -->
                                        <div id="supplierDropdown" class="purchase-search-dropdown" style="display: none;">
                                            <div class="list-group" id="supplierGrid"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Recent Used Suppliers -->
                                    <div id="recentSuppliers" class="mt-2" style="display: none;">
                                        <small class="text-muted">Recently Used:</small>
                                        <div class="d-flex flex-wrap gap-2 mt-1" id="recentSuppliersList"></div>
                                    </div>

                                    <!-- Selected supplier info -->
                                    <div id="supplierInfo" class="alert alert-info mt-3" style="display: none;">
                                        <div><strong>Selected:</strong> <span id="selectedSupplierName"></span></div>
                                        <div><small id="selectedSupplierDetails"></small></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" id="warehouse_id" name="warehouse_id" value="{{ $defaultWarehouse->id }}")>
                        <input type="hidden" id="purchase_date" name="purchase_date" value="{{ \Carbon\Carbon::today()->toDateString() }}">
                    </div>
                </div>

                <!-- PRODUCT SEARCH SECTION -->
                <div class="card mb-4 product-autocomplete-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-search"></i> Search & Add Products
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-none d-md-flex justify-content-end mb-2">
                            <button type="button" class="btn btn-primary purchase-search-action product-new-action" data-bs-toggle="modal" data-bs-target="#newProductModal">
                                <i class="bi bi-plus-lg"></i> <span>New Product</span>
                            </button>
                        </div>
                        <div class="row product-search-row">
                            <div class="col-12 product-search-column">
                                <div class="form-group mb-0">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label for="productSearch" class="form-label mb-0">Search Product</label>
                                        <button type="button" class="btn btn-primary purchase-search-action product-new-action d-md-none" data-bs-toggle="modal" data-bs-target="#newProductModal">
                                            <i class="bi bi-plus-lg"></i> <span>New Product</span>
                                        </button>
                                    </div>
                                    <div class="purchase-search-wrapper" id="productSearchWrapper">
                                        <input type="text" 
                                               id="productSearch" 
                                               class="form-control purchase-mobile-search"
                                               data-mobile-placeholder="Search by name..."
                                               placeholder="Search by name, SKU, or barcode..."
                                               autocomplete="off">

                                        <!-- Product dropdown list -->
                                        <div id="productDropdown" class="purchase-search-dropdown" style="display: none;">
                                            <div class="list-group" id="productGrid"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Recent Used Products -->
                                    <div id="recentProducts" class="mt-2" style="display: none;">
                                        <small class="text-muted">Recently Used:</small>
                                        <div class="d-flex flex-wrap gap-2 mt-1" id="recentProductsList"></div>
                                    </div>

                                </div>
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
                                <div class="h5 mb-0">Rs. <span id="subtotal">0</span></div>
                            </div>
                            <div class="col-6 text-end">
                                <label class="form-label text-muted small">Items</label>
                                <div class="h5 mb-0"><span id="itemCount">0</span></div>
                            </div>
                        </div>

                        <hr>

                        <!-- Discount Section -->
                        <div class="row mb-3">
                            <div class="col-12 col-lg-6 mb-2 mb-lg-0">
                                <label for="discountType" class="form-label">Discount Type</label>
                                <select id="discountType" class="form-select form-select-sm" onchange="updateDiscount()">
                                    <option value="amount">Amount (Rs.)</option>
                                    <option value="percentage">Percentage (%)</option>
                                </select>
                            </div>
                            <div class="col-12 col-lg-6">
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
                                <strong>Rs. <span id="display_subtotal">0</span></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">- Discount:</span>
                                <strong class="text-danger">Rs. <span id="display_discount">0</span></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">+ Transport:</span>
                                <strong>Rs. <span id="display_transport">0</span></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                                <span class="text-muted">+ Other:</span>
                                <strong>Rs. <span id="display_other">0</span></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <strong>Total Payment:</strong>
                                <strong class="h5 text-success">Rs. <span id="total_amount">0</span></strong>
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
                        <div class="mb-3 p-3 bg-warning bg-opacity-10 rounded d-none">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Remaining Payable:</span>
                                <strong class="text-warning">Rs. <span id="remaining_payable">0</span></strong>
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
                        <div class="d-grid gap-2 purchase-submit-actions">
                            <button type="submit" class="btn btn-success btn-lg mobile-purchase-action" id="submitBtn">
                                <i class="bi bi-check-circle"></i> Save & Confirm Purchase
                            </button>
                            <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary mobile-purchase-action">
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
                <form id="newSupplierForm" novalidate>
                    <div class="mb-3">
                        <label for="supplier_name" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="supplier_name" 
                               name="name" 
                               placeholder="Enter supplier name"
                               minlength="3"
                               pattern="[a-zA-Z\s]+"
                               required>
                        <div class="invalid-feedback" id="supplier_name_error" style="display: none;">
                            Please provide a valid supplier name (alphabetic, minimum 3 letters)
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="supplier_company" class="form-label">Company Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="supplier_company" 
                               name="company_name"
                               placeholder="Enter company name"
                               required>
                        <div class="invalid-feedback" id="supplier_company_error" style="display: none;">
                            Company name is required
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="supplier_phone" class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="tel" 
                               class="form-control" 
                               id="supplier_phone" 
                               name="phone"
                               placeholder="03001234567"
                               pattern="[0-9\-\+\s]+"
                               inputmode="tel"
                               required>
                        <div class="invalid-feedback" id="supplier_phone_error" style="display: none;">
                            Please provide a valid phone number (digits only)
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="supplier_email" class="form-label">Email</label>
                        <input type="email" 
                               class="form-control" 
                               id="supplier_email" 
                               name="email"
                               placeholder="example@company.com">
                        <div class="invalid-feedback" id="supplier_email_error" style="display: none;">
                            Please provide a valid email address
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="supplier_address" class="form-label">Address</label>
                        <textarea class="form-control" 
                                  id="supplier_address" 
                                  name="address" 
                                  rows="2"
                                  placeholder="Enter address"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="supplier_city" class="form-label">City</label>
                        <input type="text" 
                               class="form-control" 
                               id="supplier_city" 
                               name="city"
                               placeholder="Enter city">
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
                <form id="newProductForm" novalidate>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="product_name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="product_name" 
                                       name="name"
                                       placeholder="Enter product name"
                                       autocomplete="off"
                                       required>
                                <style>
                                    #product_name::-webkit-calendar-picker-indicator,
                                    #product_name::-webkit-search-cancel-button {
                                        display: none;
                                    }
                                    
                                    /* Fix autocomplete dropdown styling */
                                    #product_name {
                                        background-image: none !important;
                                        background-color: white !important;
                                    }
                                    
                                    /* Remove autocomplete blue background on focus */
                                    input:-webkit-autofill,
                                    input:-webkit-autofill:hover,
                                    input:-webkit-autofill:focus,
                                    input:-webkit-autofill:active {
                                        -webkit-box-shadow: 0 0 0 30px white inset !important;
                                        box-shadow: 0 0 0 30px white inset !important;
                                    }
                                    
                                    /* Remove blue text on autofill */
                                    input:-webkit-autofill {
                                        -webkit-text-fill-color: #333 !important;
                                    }
                                </style>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="product_base_unit" class="form-label">Base Unit <span class="text-danger">*</span></label>
                                <select class="form-select" 
                                        id="product_base_unit" 
                                        name="base_unit_id"
                                        required>
                                    <option value="">-- Select Base Unit --</option>
                                    @foreach(\App\Models\Unit::active()->orderBy('name')->get() as $unit)
                                        <option value="{{ $unit->id }}" data-abbr="{{ $unit->abbreviation }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">Primary unit for inventory tracking</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="product_minimum_stock_level" class="form-label">Minimum Stock Level</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="product_minimum_stock_level" 
                                       name="minimum_stock_level" 
                                       value="10"
                                       min="0" step="1"
                                       inputmode="numeric"
                                       onwheel="return false">
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- ── Bag / Box Checkbox ── --}}
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   id="modal_has_package">
                            <label class="form-check-label fw-semibold" for="modal_has_package">
                                This product is sold / purchased as Bag or Box
                            </label>
                        </div>
                        <small class="text-muted d-block ms-4">Check this if you buy/sell by the bag or box (e.g., 1 Bag = 50 KG).</small>
                    </div>

                    {{-- Section A: Normal (unchecked) — unit prices --}}
                    <div id="modalSectionNormal">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_purchase_price" class="form-label" id="modalNormalCostLabel">Unit Cost Price (Rs.) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control"
                                           id="product_purchase_price"
                                           step="0.01" min="0"
                                           placeholder="e.g., 180"
                                           inputmode="decimal" onwheel="return false">
                                    <small class="text-muted d-block mt-1" id="modalNormalCostHint">Cost price per base unit.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_sale_price" class="form-label" id="modalNormalSaleLabel">Unit Sell Price (Rs.) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control"
                                           id="product_sale_price"
                                           step="0.01" min="0"
                                           placeholder="e.g., 220"
                                           inputmode="decimal" onwheel="return false">
                                    <small class="text-muted d-block mt-1" id="modalNormalSaleHint">Sale price per base unit.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section B: Package (checked) — Bag/Box prices --}}
                    <div id="modalSectionPackage" style="display:none;">

                        {{-- Package Type --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Package Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio"
                                           name="modal_package_type" id="modalPkgBag" value="Bag" checked>
                                    <label class="form-check-label" for="modalPkgBag">Bag</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio"
                                           name="modal_package_type" id="modalPkgBox" value="Box">
                                    <label class="form-check-label" for="modalPkgBox">Box</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" id="modalConversionLabel">Bag/Box Quantity <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control"
                                               id="modal_pkg_conversion"
                                               min="0.0001" step="0.0001" placeholder="e.g., 50">
                                        <span class="input-group-text" id="modalUnitAbbrBadge">units</span>
                                    </div>
                                    <small class="text-muted d-block mt-1">How many base units in one package.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" id="modalPkgCostLabel">Bag Cost Price (Rs.) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control"
                                           id="modal_pkg_purchase_price"
                                           min="0" step="0.01" placeholder="e.g., 4500">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" id="modalPkgSaleLabel">Bag Sell Price (Rs.) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control"
                                           id="modal_pkg_sale_price"
                                           min="0" step="0.01" placeholder="e.g., 5000">
                                </div>
                            </div>
                        </div>

                        <div id="modalBasePricePreview" class="alert alert-info py-2 small" style="display:none;">
                            <strong>Base unit prices (auto-calculated):</strong>
                            <span id="modalBasePriceText"></span>
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
        const mobileSearchQuery = window.matchMedia('(max-width: 575.98px)');
        const syncMobileSearchPlaceholders = () => {
            document.querySelectorAll('[data-mobile-placeholder]').forEach(input => {
                if (!input.dataset.desktopPlaceholder) {
                    input.dataset.desktopPlaceholder = input.placeholder;
                }
                input.placeholder = mobileSearchQuery.matches
                    ? input.dataset.mobilePlaceholder
                    : input.dataset.desktopPlaceholder;
            });
        };
        syncMobileSearchPlaceholders();
        mobileSearchQuery.addEventListener('change', syncMobileSearchPlaceholders);

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
                const supplierSearch = document.getElementById('supplierSearch');
                if (document.activeElement === supplierSearch) {
                    supplierSearch.dispatchEvent(new Event('input', { bubbles: true }));
                }
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
                const productSearch = document.getElementById('productSearch');
                if (document.activeElement === productSearch) {
                    productSearch.dispatchEvent(new Event('input', { bubbles: true }));
                }
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
        // Initialize modal package UI after DOM is ready
        initModalPackageUI();

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
            supplierDropdown.style.display = 'block';
        });

        clearSupplierBtn.addEventListener('click', function() {
            supplierSearch.value = '';
            supplierDropdown.style.display = 'none';
            document.getElementById('supplier_id').value = '';
            document.getElementById('supplierInfo').style.display = 'none';
            currentSupplier = null;
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
            productDropdown.style.display = 'block';
        });

        // Close dropdowns on outside click
        document.addEventListener('click', function(event) {
            if (!event.target.closest('#supplierSearchWrapper')) {
                supplierDropdown.style.display = 'none';
            }
            if (!event.target.closest('#productSearchWrapper')) {
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

        // Product Units Management in Modal
        let modalUnitIndex = 0;
        const modalUnitsContainer = document.getElementById('modalProductUnitsContainer');
        const modalAddUnitBtn = document.getElementById('modalAddUnitBtn');
        const baseUnitSelect = document.getElementById('product_base_unit');

        if (modalAddUnitBtn && baseUnitSelect) {
            modalAddUnitBtn.addEventListener('click', function() {
                if (!baseUnitSelect.value) {
                    baseUnitSelect.focus();
                    baseUnitSelect.classList.add('is-invalid');
                    return;
                }
                baseUnitSelect.classList.remove('is-invalid');

                const unitRow = document.createElement('div');
                unitRow.className = 'card mb-2 modal-unit-item';
                unitRow.innerHTML = `
                    <div class="card-body py-2">
                        <div class="row align-items-center">
                            <input type="hidden" name="product_units[${modalUnitIndex}][unit_id]" value="${baseUnitSelect.value}">
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Unit</label>
                                <input type="text" class="form-control form-control-sm" 
                                       value="${baseUnitSelect.options[baseUnitSelect.selectedIndex].text}" 
                                       disabled style="background:#e9ecef">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Package Name</label>
                                <input type="text" class="form-control form-control-sm" 
                                       name="product_units[${modalUnitIndex}][package_name]" 
                                       placeholder="e.g., bag, box">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Conversion <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm modal-conversion-input" 
                                       name="product_units[${modalUnitIndex}][conversion_to_base]" 
                                       min="0.0001" step="0.01" placeholder="1.0" required
                                       data-index="${modalUnitIndex}">
                                <small class="text-muted modal-conversion-display"></small>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Purchase Price</label>
                                <input type="number" class="form-control form-control-sm modal-purchase-price" 
                                       name="product_units[${modalUnitIndex}][purchase_price]" 
                                       min="0" step="0.01" placeholder="Auto"
                                       data-index="${modalUnitIndex}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Sale Price</label>
                                <input type="number" class="form-control form-control-sm modal-sale-price" 
                                       name="product_units[${modalUnitIndex}][sale_price]" 
                                       min="0" step="0.01" placeholder="Auto"
                                       data-index="${modalUnitIndex}">
                            </div>
                            <div class="col-md-2 text-end">
                                <label class="form-label small d-block mb-1">&nbsp;</label>
                                <button type="button" class="btn btn-sm btn-danger modal-remove-unit-btn">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                modalUnitsContainer.appendChild(unitRow);

                // Attach event listeners
                const conversionInput = unitRow.querySelector('.modal-conversion-input');
                const purchasePriceInput = unitRow.querySelector('.modal-purchase-price');
                const salePriceInput = unitRow.querySelector('.modal-sale-price');
                const conversionDisplay = unitRow.querySelector('.modal-conversion-display');
                const removeBtn = unitRow.querySelector('.modal-remove-unit-btn');

                let purchasePriceManuallyEdited = false;
                let salePriceManuallyEdited = false;

                purchasePriceInput.addEventListener('input', () => { purchasePriceManuallyEdited = true; });
                salePriceInput.addEventListener('input', () => { salePriceManuallyEdited = true; });

                function updateConversionDisplay() {
                    const conversion = parseFloat(conversionInput.value) || 1;
                    const baseAbbr = baseUnitSelect.options[baseUnitSelect.selectedIndex]?.text.match(/\(([^)]+)\)/)?.[1] || '';
                    const selectedAbbr = baseUnitSelect.options[baseUnitSelect.selectedIndex]?.text.match(/\(([^)]+)\)/)?.[1] || '';
                    if (baseAbbr) {
                        conversionDisplay.textContent = `1 unit = ${conversion} ${baseAbbr}`;
                    }
                }

                function autoCalculatePrices() {
                    const conversion = parseFloat(conversionInput.value);
                    if (!conversion || conversion <= 0) return;

                    const basePurchasePrice = parseFloat(document.getElementById('product_purchase_price').value) || 0;
                    const baseSalePrice = parseFloat(document.getElementById('product_sale_price').value) || 0;

                    if (!purchasePriceManuallyEdited && basePurchasePrice > 0) {
                        purchasePriceInput.value = (basePurchasePrice * conversion).toFixed(2);
                    }

                    if (!salePriceManuallyEdited && baseSalePrice > 0) {
                        salePriceInput.value = (baseSalePrice * conversion).toFixed(2);
                    }
                }

                conversionInput.addEventListener('input', function() {
                    updateConversionDisplay();
                    autoCalculatePrices();
                });

                document.getElementById('product_purchase_price')?.addEventListener('input', function() {
                    if (!purchasePriceManuallyEdited) autoCalculatePrices();
                });

                document.getElementById('product_sale_price')?.addEventListener('input', function() {
                    if (!salePriceManuallyEdited) autoCalculatePrices();
                });

                removeBtn.addEventListener('click', function() {
                    unitRow.remove();
                });

                // Focus on package name
                unitRow.querySelector('input[name*="[package_name]"]').focus();

                modalUnitIndex++;
            });
        }
    }

    // ========== SUPPLIER FUNCTIONS ==========
    function displaySupplierResults(suppliers) {
        const grid = document.getElementById('supplierGrid');
        grid.innerHTML = '';

        // Show all matching suppliers; the dropdown itself is scrollable.
        const limited = suppliers;

        if (limited.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'list-group-item text-muted';
            empty.textContent = 'No suppliers found';
            grid.appendChild(empty);
            return;
        }

        limited.forEach(supplier => {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'list-group-item list-group-item-action';

            const name = document.createElement('span');
            name.className = 'd-block fw-semibold';
            name.textContent = supplier.name || '';
            option.appendChild(name);

            const details = [supplier.company_name, supplier.phone].filter(Boolean).join(' · ');
            if (details) {
                const meta = document.createElement('small');
                meta.className = 'd-block text-muted';
                meta.textContent = details;
                option.appendChild(meta);
            }

            option.addEventListener('click', function(e) {
                e.preventDefault();
                selectSupplier(supplier);
            });

            grid.appendChild(option);
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
    }

    function saveNewSupplier() {
        const form = document.getElementById('newSupplierForm');
        const nameInput = document.getElementById('supplier_name');
        const companyInput = document.getElementById('supplier_company');
        const phoneInput = document.getElementById('supplier_phone');
        const emailInput = document.getElementById('supplier_email');
        
        // Clear previous error states
        const fields = [nameInput, companyInput, phoneInput, emailInput];
        fields.forEach(field => {
            field.classList.remove('is-invalid');
            const feedback = field.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.style.display = 'none';
            }
        });
        
        // Validation flags
        let isValid = true;
        
        // 1. SUPPLIER NAME validation
        if (!nameInput.value.trim()) {
            showFieldError(nameInput, 'Supplier name is required');
            isValid = false;
        } else if (nameInput.value.trim().length < 3) {
            showFieldError(nameInput, 'Supplier name must be at least 3 letters');
            isValid = false;
        } else if (!/^[a-zA-Z\s]+$/.test(nameInput.value.trim())) {
            showFieldError(nameInput, 'Supplier name must contain only alphabetic characters');
            isValid = false;
        }
        
        // 2. COMPANY NAME validation
        if (!companyInput.value.trim()) {
            showFieldError(companyInput, 'Company name is required');
            isValid = false;
        }
        
        // 3. PHONE validation
        if (!phoneInput.value.trim()) {
            showFieldError(phoneInput, 'Phone number is required');
            isValid = false;
        } else if (!/^[0-9\-\+\s]+$/.test(phoneInput.value.trim())) {
            showFieldError(phoneInput, 'Phone number must contain only digits, spaces, hyphens, or plus sign');
            isValid = false;
        } else if (phoneInput.value.trim().replace(/[^\d]/g, '').length < 10) {
            showFieldError(phoneInput, 'Phone number must be at least 10 digits');
            isValid = false;
        }
        
        // 4. EMAIL validation (optional but if provided must be valid)
        if (emailInput.value.trim()) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailInput.value.trim())) {
                showFieldError(emailInput, 'Please provide a valid email address');
                isValid = false;
            }
        }
        
        // If validation fails, stop here
        if (!isValid) {
            showAlert('danger', 'Please correct the errors above');
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
    
    // Helper function to show field error
    function showFieldError(field, message) {
        field.classList.add('is-invalid');
        
        // Find the error div by ID pattern
        const errorDivId = field.id + '_error';
        const feedback = document.getElementById(errorDivId);
        
        if (feedback) {
            feedback.textContent = message;
            feedback.style.display = 'block';
            feedback.classList.add('d-block');
        } else {
            console.warn('Error feedback div not found for:', errorDivId);
        }
    }

    // ========== PRODUCT FUNCTIONS ==========
    function displayProductResults(products) {
        const grid = document.getElementById('productGrid');
        grid.innerHTML = '';

        // Show all matches; the dropdown scrolls after five visible results.
        const limited = products;

        if (limited.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'list-group-item text-muted';
            empty.textContent = 'No products found';
            grid.appendChild(empty);
            return;
        }

        limited.forEach(product => {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';

            const name = document.createElement('span');
            name.className = 'fw-semibold';
            name.textContent = product.name || '';
            option.appendChild(name);

            const price = document.createElement('small');
            price.className = 'text-muted ms-3';
            price.textContent = `Rs. ${Number(product.purchase_price || 0).toLocaleString()}`;
            option.appendChild(price);

            option.addEventListener('click', function(e) {
                e.preventDefault();
                addProductToItems(product);
            });

            grid.appendChild(option);
        });
    }

    function addProductToItems(product) {
        // VALIDATION: Check if supplier is selected
        const supplierId = document.getElementById('supplier_id').value;
        const supplierSearchInput = document.getElementById('supplierSearch');
        if (!supplierId) {
            // Focus on supplier input
            supplierSearchInput.focus();
            
            // Auto-scroll to supplier section
            document.querySelector('.card:first-of-type')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            return; // Exit function, don't add product
        }
        
        // Check if product already exists
        const existingItem = purchaseItems.find(item => item.product_id === product.id);
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            // Multi-unit support: Get available units for this product
            const productUnits = product.product_units || [];
            
            // Determine default unit:
            // 1. If only one unit exists, use it
            // 2. Otherwise, use base unit
            // 3. Fallback: use first active unit or create legacy unit
            let defaultUnit = null;
            
            if (productUnits.length === 1) {
                defaultUnit = productUnits[0];
            } else if (productUnits.length > 1) {
                // Try to find base unit
                defaultUnit = productUnits.find(u => u.is_base_unit) || productUnits[0];
            } else {
                // Legacy product without product_units - create a virtual unit from product data
                defaultUnit = {
                    unit_id: product.base_unit_id || null,
                    unit_name: product.unit || 'Unit',
                    unit_abbreviation: product.unit || 'Unit',
                    conversion_to_base: 1,
                    purchase_price: product.purchase_price,
                    sale_price: product.sale_price,
                    is_base_unit: true
                };
            }
            
            purchaseItems.push({
                product_id: product.id,
                product_name: product.name,
                quantity: 1,
                product_unit_id: defaultUnit.id,  // Add ProductUnit ID
                unit_id: defaultUnit.unit_id,
                unit_name: defaultUnit.unit_name,
                unit_abbreviation: defaultUnit.unit_abbreviation,
                package_name: defaultUnit.package_name || null,  // Add package name
                conversion_to_base: defaultUnit.conversion_to_base,
                unit_price: parseFloat(defaultUnit.purchase_price ?? product.purchase_price),
                sale_price: parseFloat(defaultUnit.sale_price ?? product.sale_price),
                // Store full product data including available units
                product_units: productUnits,
                base_unit_abbreviation: product.base_unit?.abbreviation || product.unit || 'Unit',
                // Legacy field for backward compatibility
                unit: defaultUnit.unit_abbreviation
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
        const form          = document.getElementById('newProductForm');
        const nameInput     = document.getElementById('product_name');
        const baseUnitInput = document.getElementById('product_base_unit');
        const purchasePriceInput = document.getElementById('product_purchase_price');
        const salePriceInput     = document.getElementById('product_sale_price');

        const hasPackage    = document.getElementById('modal_has_package').checked;
        const pkgConvInput  = document.getElementById('modal_pkg_conversion');
        const pkgCostInput  = document.getElementById('modal_pkg_purchase_price');
        const pkgSaleInput  = document.getElementById('modal_pkg_sale_price');
        const pkgType       = document.querySelector('input[name="modal_package_type"]:checked')?.value || 'Bag';

        // ── Clear previous error states ────────────────────────────────
        [nameInput, baseUnitInput, purchasePriceInput, salePriceInput,
         pkgConvInput, pkgCostInput, pkgSaleInput].forEach(f => f?.classList.remove('is-invalid'));

        const markInvalid = field => {
            field.classList.add('is-invalid');
            field.scrollIntoView({ behavior: 'smooth', block: 'center' });
            field.focus({ preventScroll: true });
        };

        // ── Required field validation ──────────────────────────────────
        if (!nameInput.value.trim())         { markInvalid(nameInput);     return; }
        if (!baseUnitInput.value)            { markInvalid(baseUnitInput); return; }

        if (hasPackage) {
            // Package mode: validate package fields
            const conv = parseFloat(pkgConvInput.value);
            if (!conv || conv <= 0)                    { markInvalid(pkgConvInput);  return; }
            const cost = parseFloat(pkgCostInput.value);
            if (pkgCostInput.value === '' || cost < 0) { markInvalid(pkgCostInput);  return; }
            const sale = parseFloat(pkgSaleInput.value);
            if (pkgSaleInput.value === '' || sale < 0) { markInvalid(pkgSaleInput);  return; }
        } else {
            // Normal mode: validate base prices
            const cost = Number(purchasePriceInput.value);
            if (purchasePriceInput.value === '' || !Number.isFinite(cost) || cost < 0) {
                markInvalid(purchasePriceInput); return;
            }
            const sale = Number(salePriceInput.value);
            if (salePriceInput.value === '' || !Number.isFinite(sale) || sale < 0) {
                markInvalid(salePriceInput); return;
            }
        }

        // ── Build FormData ─────────────────────────────────────────────
        const formData = new FormData();
        formData.append('name',              nameInput.value.trim());
        formData.append('base_unit_id',      baseUnitInput.value);
        formData.append('minimum_stock_level',
            document.getElementById('product_minimum_stock_level')?.value || '10');

        if (hasPackage) {
            const conv    = parseFloat(pkgConvInput.value);
            const pkgCost = parseFloat(pkgCostInput.value);
            const pkgSale = parseFloat(pkgSaleInput.value);

            // Derive base prices from package prices
            const baseCost = pkgCost / conv;
            const baseSale = pkgSale / conv;

            formData.append('purchase_price', baseCost.toFixed(4));
            formData.append('sale_price',     baseSale.toFixed(4));

            // Row 0: base unit (NULL package_name, conversion 1)
            formData.append('product_units[0][unit_id]',           baseUnitInput.value);
            formData.append('product_units[0][package_name]',      '');
            formData.append('product_units[0][conversion_to_base]','1');
            formData.append('product_units[0][purchase_price]',    baseCost.toFixed(4));
            formData.append('product_units[0][sale_price]',        baseSale.toFixed(4));

            // Row 1: Bag / Box
            formData.append('product_units[1][unit_id]',           baseUnitInput.value);
            formData.append('product_units[1][package_name]',      pkgType);
            formData.append('product_units[1][conversion_to_base]',conv.toString());
            formData.append('product_units[1][purchase_price]',    pkgCost.toString());
            formData.append('product_units[1][sale_price]',        pkgSale.toString());
        } else {
            formData.append('purchase_price', purchasePriceInput.value);
            formData.append('sale_price',     salePriceInput.value);
        }

        // ── POST to storeAjax ──────────────────────────────────────────
        fetch('{{ route("admin.products.storeAjax") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.status === 422) {
                return response.json().then(errors => {
                    console.error('Validation errors:', errors);
                    const firstField = Object.keys(errors.errors || {})[0];
                    const fieldMap = {
                        name: nameInput,
                        base_unit_id: baseUnitInput,
                        purchase_price: hasPackage ? pkgCostInput : purchasePriceInput,
                        sale_price:     hasPackage ? pkgSaleInput  : salePriceInput,
                    };
                    if (fieldMap[firstField]) { markInvalid(fieldMap[firstField]); }
                    throw new Error('Product validation failed');
                });
            }
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.message || 'Server error: ' + response.statusText);
                }).catch(() => {
                    throw new Error('Server error (' + response.status + '): ' + response.statusText);
                });
            }
            return response.json();
        })
        .then(data => {
            // Merge full product data into allProducts
            allProducts.push(data);
            addProductToItems(data);
            bootstrap.Modal.getInstance(document.getElementById('newProductModal')).hide();
            // Reset form and package UI
            form.reset();
            const hasPkgCb = document.getElementById('modal_has_package');
            if (hasPkgCb) hasPkgCb.checked = false;
            const pkgSection = document.getElementById('modalSectionPackage');
            if (pkgSection) pkgSection.style.display = 'none';
            const normalSection = document.getElementById('modalSectionNormal');
            if (normalSection) normalSection.style.display = '';
            const previewEl = document.getElementById('modalBasePricePreview');
            if (previewEl) previewEl.style.display = 'none';
            showAlert('success', 'Product created successfully.');
        })
        .catch(error => {
            console.error('Error:', error);
            if (error.message !== 'Product validation failed') {
                showAlert('danger', 'Error creating product: ' + error.message);
            }
        });
    }

    // ── Modal package UI logic (runs after DOM is ready) ──────────────────
    function initModalPackageUI() {
        const hasPackageCb   = document.getElementById('modal_has_package');
        const sectionNormal  = document.getElementById('modalSectionNormal');
        const sectionPkg     = document.getElementById('modalSectionPackage');
        const baseUnitSelect = document.getElementById('product_base_unit');
        const unitAbbrBadge  = document.getElementById('modalUnitAbbrBadge');
        const convLabel      = document.getElementById('modalConversionLabel');
        const costLabel      = document.getElementById('modalPkgCostLabel');
        const saleLabel      = document.getElementById('modalPkgSaleLabel');
        const normalCostLabel = document.getElementById('modalNormalCostLabel');
        const normalSaleLabel = document.getElementById('modalNormalSaleLabel');
        const normalCostHint  = document.getElementById('modalNormalCostHint');
        const normalSaleHint  = document.getElementById('modalNormalSaleHint');
        const pkgConvInput   = document.getElementById('modal_pkg_conversion');
        const pkgCostInput   = document.getElementById('modal_pkg_purchase_price');
        const pkgSaleInput   = document.getElementById('modal_pkg_sale_price');
        const previewBox     = document.getElementById('modalBasePricePreview');
        const previewText    = document.getElementById('modalBasePriceText');

        function abbr() {
            const opt = baseUnitSelect.options[baseUnitSelect.selectedIndex];
            return opt?.dataset?.abbr || opt?.text?.match(/\(([^)]+)\)/)?.[1] || 'units';
        }
        function pkgType() {
            return document.getElementById('modalPkgBag').checked ? 'Bag' : 'Box';
        }
        function updateLabels() {
            const a  = abbr();
            const pt = pkgType();
            // Package section labels
            unitAbbrBadge.textContent = a;
            convLabel.innerHTML = `${pt} Quantity / Weight <span class="text-danger">*</span>`;
            costLabel.innerHTML = `${pt} Cost Price (Rs.) <span class="text-danger">*</span>`;
            saleLabel.innerHTML = `${pt} Sell Price (Rs.) <span class="text-danger">*</span>`;
            // Normal section labels
            if (normalCostLabel) normalCostLabel.innerHTML = `Unit Cost Price (Rs.) <span class="text-danger">*</span>`;
            if (normalSaleLabel) normalSaleLabel.innerHTML = `Unit Sell Price (Rs.) <span class="text-danger">*</span>`;
            if (normalCostHint)  normalCostHint.textContent  = a !== 'units' ? `Cost price per 1 ${a}.` : 'Cost price per base unit.';
            if (normalSaleHint)  normalSaleHint.textContent  = a !== 'units' ? `Sale price per 1 ${a}.` : 'Sale price per base unit.';
        }
        function applyMode() {
            const isPkg = hasPackageCb.checked;
            sectionNormal.style.display = isPkg ? 'none' : '';
            sectionPkg.style.display    = isPkg ? ''     : 'none';
            if (!isPkg) previewBox.style.display = 'none';
            updateLabels();
        }
        function updatePreview() {
            const conv = parseFloat(pkgConvInput.value);
            const cost = parseFloat(pkgCostInput.value);
            const sale = parseFloat(pkgSaleInput.value);
            if (conv > 0 && (cost > 0 || sale > 0)) {
                const a  = abbr();
                const parts = [];
                if (cost > 0) parts.push(`Cost per ${a}: Rs. ${(cost/conv).toFixed(2)}`);
                if (sale > 0) parts.push(`Sale per ${a}: Rs. ${(sale/conv).toFixed(2)}`);
                previewText.textContent = ' ' + parts.join(' | ');
                previewBox.style.display = '';
            } else {
                previewBox.style.display = 'none';
            }
        }

        hasPackageCb.addEventListener('change', applyMode);
        baseUnitSelect.addEventListener('change', function() { updateLabels(); updatePreview(); });
        [document.getElementById('modalPkgBag'), document.getElementById('modalPkgBox')]
            .forEach(r => r?.addEventListener('change', updateLabels));
        [pkgConvInput, pkgCostInput, pkgSaleInput]
            .forEach(el => el?.addEventListener('input', updatePreview));

        applyMode();
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
        if (activeElement && (activeElement.tagName === 'INPUT' || activeElement.tagName === 'SELECT')) {
            // Find which row and field is active
            const row = activeElement.closest('tr');
            if (row) {
                activeIndex = Array.from(tbody.children).indexOf(row);
                // Determine which field (quantity, unit_id, unit_price, or sale_price)
                if (activeElement.getAttribute('data-field')) {
                    activeField = activeElement.getAttribute('data-field');
                }
                if (activeElement.tagName === 'INPUT') {
                    cursorPosition = activeElement.selectionStart;
                }
            }
        }

        if (purchaseItems.length === 0) {
            tbody.innerHTML = '<tr id="emptyRow" class="text-center text-muted"><td colspan="7" class="py-3"><i class="bi bi-inbox"></i> No items added yet. Search and select products above.</td></tr>';
            return;
        }

        tbody.innerHTML = purchaseItems.map((item, index) => {
            // Build unit selector dropdown
            let unitOptions = '';
            
            if (item.product_units && item.product_units.length > 0) {
                // Multi-unit product - show all active units
                unitOptions = item.product_units.map(pu => {
                    const selected = pu.id === item.product_unit_id ? 'selected' : '';

                    // A ProductUnit is the base when it has no package_name AND conversion = 1.
                    // Do NOT rely on pu.is_base_unit from the API — that field was computed
                    // server-side and may be stale when the same unit_id is shared by multiple packages.
                    const isBase = (!pu.package_name || pu.package_name === '')
                        && Math.abs(parseFloat(pu.conversion_to_base) - 1.0) < 0.0001;

                    // Display name: use package_name when set, otherwise unit_name
                    const displayName = pu.package_name || pu.unit_name;
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
                conversionPreview = `<div class="text-muted" style="font-size: 0.75rem; margin-top: 2px;">
                    ${item.quantity} × ${item.conversion_to_base} = ${baseQty} ${item.base_unit_abbreviation}
                </div>`;
            }
            
            return `
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
                    ${conversionPreview}
                </td>
                <td>
                    <select class="form-select form-select-sm" 
                            data-field="unit_id"
                            data-index="${index}"
                            onchange="updateItemUnit(${index}, this.value)"
                            ${item.product_units && item.product_units.length > 1 ? '' : 'disabled'}>
                        ${unitOptions}
                    </select>
                    <!-- Display current selection for closed dropdown -->
                    <small class="text-muted d-block" style="font-size: 0.7rem; margin-top: 2px;">
                        ${item.package_name ? item.package_name : item.unit_abbreviation}
                    </small>
                </td>
                <td>
                    <input type="number" 
                           class="form-control form-control-sm" 
                           value="${Math.round(item.unit_price)}" 
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
                           value="${Math.round(item.sale_price)}" 
                           min="0" 
                           step="0.01"
                           data-field="sale_price"
                           data-index="${index}"
                           oninput="updateItemSalePrice(${index}, this.value)"
                           onblur="updateItemSalePrice(${index}, this.value)">
                </td>
                <td>
                    <strong>Rs. ${Math.round(item.quantity * item.unit_price)}</strong>
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
        `;
        }).join('');

        // Restore focus and cursor position if there was an active element
        if (activeIndex >= 0 && activeField) {
            const newRow = tbody.children[activeIndex];
            if (newRow) {
                const input = newRow.querySelector(`[data-field="${activeField}"]`);
                if (input) {
                    input.focus();
                    if (input.tagName === 'INPUT') {
                        input.setSelectionRange(cursorPosition, cursorPosition);
                    }
                }
            }
        }
    }

    function updateItemQuantity(index, value) {
        purchaseItems[index].quantity = parseFloat(value) || 0;
        // Update the row with new quantity and conversion preview
        updateRowWithConversion(index);
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

    /**
     * Handle unit change for a purchase item
     * Updates conversion factor, prices, and re-renders to show new conversion preview
     */
    function updateItemUnit(index, productUnitId) {
        const item = purchaseItems[index];
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
        
        // Update prices with unit-specific prices if available
        // Use fallback to product base prices if unit-specific price is null
        const productBasePrice = item.unit_price; // Keep current if not specified
        const productBaseSalePrice = item.sale_price;
        
        // Only update price if the unit has a specific price configured
        if (selectedUnit.purchase_price !== null && selectedUnit.purchase_price !== undefined) {
            item.unit_price = parseFloat(selectedUnit.purchase_price);
        }
        
        if (selectedUnit.sale_price !== null && selectedUnit.sale_price !== undefined) {
            item.sale_price = parseFloat(selectedUnit.sale_price);
        }
        
        // Re-render the table to update conversion preview and prices
        renderItemsTable();
        updateCalculations();
    }

    // Update the total display for a specific row without re-rendering
    function updateRowTotal(index) {
        const tbody = document.getElementById('itemsBody');
        const row = tbody.children[index];
        if (row) {
            const item = purchaseItems[index];
            const totalCell = row.cells[5]; // 6th column (0-indexed) is the Total column
            if (totalCell) {
                totalCell.innerHTML = `<strong>Rs. ${Math.round(item.quantity * item.unit_price)}</strong>`;
            }
        }
    }

    /**
     * Update row total AND conversion preview when quantity changes
     */
    function updateRowWithConversion(index) {
        const tbody = document.getElementById('itemsBody');
        const row = tbody.children[index];
        if (row) {
            const item = purchaseItems[index];
            
            // Update total cell (6th column, 0-indexed = 5)
            const totalCell = row.cells[5];
            if (totalCell) {
                totalCell.innerHTML = `<strong>Rs. ${Math.round(item.quantity * item.unit_price)}</strong>`;
            }
            
            // Update conversion preview (inside quantity cell, 2nd column, 0-indexed = 1)
            const quantityCell = row.cells[1];
            if (quantityCell && item.conversion_to_base && item.conversion_to_base !== 1) {
                const baseQty = (item.quantity * item.conversion_to_base).toFixed(2);
                const conversionPreview = `<div class="text-muted" style="font-size: 0.75rem; margin-top: 2px;">
                    ${item.quantity} × ${item.conversion_to_base} = ${baseQty} ${item.base_unit_abbreviation}
                </div>`;
                
                // Find the input element and add preview after it
                const input = quantityCell.querySelector('input');
                const existingPreview = quantityCell.querySelector('.text-muted');
                
                if (existingPreview) {
                    existingPreview.outerHTML = conversionPreview;
                } else if (input) {
                    input.insertAdjacentHTML('afterend', conversionPreview);
                }
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
        
        document.getElementById('subtotal').textContent = Math.round(subtotal);
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
        document.getElementById('subtotal').textContent = Math.round(subtotal);
        document.getElementById('display_subtotal').textContent = Math.round(subtotal);
        document.getElementById('display_discount').textContent = Math.round(discount);
        document.getElementById('display_transport').textContent = Math.round(transportCost);
        document.getElementById('display_other').textContent = Math.round(otherExpenses);
        document.getElementById('total_amount').textContent = Math.round(totalAmount);
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

        document.getElementById('remaining_payable').textContent = Math.round(remainingPayable);

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
    function focusPurchaseField(field) {
        if (!field) {
            return;
        }

        field.scrollIntoView({ behavior: 'smooth', block: 'center' });
        field.focus({ preventScroll: true });
    }

    function checkPurchaseFormBeforeSubmit() {
        const supplierId = document.getElementById('supplier_id').value;
        const supplierSearch = document.getElementById('supplierSearch');
        const productSearch = document.getElementById('productSearch');

        if (!supplierId) {
            focusPurchaseField(supplierSearch);
            return false;
        }

        if (purchaseItems.length === 0) {
            focusPurchaseField(productSearch);
            return false;
        }

        const firstInvalidItemField = Array.from(document.querySelectorAll('#itemsBody input[data-field]'))
            .find(field => {
                const value = parseFloat(field.value);
                return !Number.isFinite(value) || (field.dataset.field === 'quantity' && value <= 0);
            });

        if (firstInvalidItemField) {
            focusPurchaseField(firstInvalidItemField);
            return false;
        }

        return true;
    }

    document.getElementById('purchaseForm').addEventListener('submit', function(e) {
        e.preventDefault();

        if (!checkPurchaseFormBeforeSubmit()) {
            return;
        }

        document.getElementById('items').value = JSON.stringify(purchaseItems);

        this.submit();
    });

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

    .purchase-search-wrapper {
        position: relative;
        overflow: visible;
    }

    @media (min-width: 768px) {
        .supplier-search-column .purchase-search-wrapper,
        .product-search-column .purchase-search-wrapper {
            width: 65%;
        }
    }

    .purchase-search-action {
        display: inline-flex;
        width: auto;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.45rem 0.75rem;
        font-size: 0.95rem;
        line-height: 1.25;
        white-space: nowrap;
    }

    .supplier-new-action {
        padding: 0.5rem 0.85rem;
        font-size: 1rem;
    }

    @media (max-width: 767.98px) {
        .purchase-search-action {
            padding: 0.15rem 0.35rem;
            font-size: 0.65rem;
            gap: 0.2rem;
        }

        .supplier-new-action {
            padding: 0.18rem 0.4rem;
            font-size: 0.68rem;
        }

        .purchase-submit-actions .mobile-purchase-action {
            padding: 0.3rem 0.55rem;
            font-size: 0.8rem;
            line-height: 1.25;
        }
    }

    @media (max-width: 1024px) {
        .supplier-autocomplete-card,
        .supplier-autocomplete-card .card-body,
        .supplier-autocomplete-card .supplier-search-row,
        .supplier-autocomplete-card .supplier-search-column,
        .supplier-autocomplete-card .form-group,
        .product-autocomplete-card,
        .product-autocomplete-card .card-body,
        .product-autocomplete-card .product-search-row,
        .product-autocomplete-card .product-search-column,
        .product-autocomplete-card .form-group {
            overflow: visible !important;
        }

        .supplier-autocomplete-card:focus-within,
        .product-autocomplete-card:focus-within {
            position: relative;
            z-index: 1060;
        }
    }

    .purchase-search-dropdown {
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

    .purchase-search-dropdown .list-group-item {
        border-left: 0;
        border-right: 0;
        text-align: left;
    }

    .purchase-search-dropdown .list-group-item:first-child {
        border-top: 0;
    }

    .purchase-search-dropdown .list-group-item:last-child {
        border-bottom: 0;
    }

    #productDropdown {
        max-height: 242px;
    }

    #productGrid > .list-group-item {
        height: 48px;
        min-height: 48px;
        overflow: hidden;
    }

    #productGrid > .list-group-item > .fw-semibold {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    #productGrid > .list-group-item > small {
        flex-shrink: 0;
        white-space: nowrap;
    }

    #recentSuppliers,
    #recentProducts {
        display: none !important;
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

    /* ========== FIX: Remove black autocomplete dropdown ========== */
    /* Disable browser autocomplete dropdown styling */
    input[autocomplete="off"]:-webkit-autofill,
    input[autocomplete="off"]:-webkit-autofill:hover,
    input[autocomplete="off"]:-webkit-autofill:focus {
        -webkit-box-shadow: 0 0 0 1000px white inset !important;
        box-shadow: 0 0 0 1000px white inset !important;
        -webkit-text-fill-color: #333 !important;
    }

    /* Hide browser's datalist dropdown */
    input::selection {
        background: transparent;
    }

    /* Remove autocomplete highlight color */
    input:autofill {
        -webkit-text-fill-color: #333 !important;
        -webkit-box-shadow: 0 0 0 1000px white inset !important;
        caret-color: #333 !important;
    }

    /* Ensure modal is above autocomplete */
    .modal {
        z-index: 9999 !important;
    }

    .modal-backdrop {
        z-index: 9998 !important;
    }

    @media (max-width: 1024px) {
        #newSupplierModal .modal-dialog {
            width: min(92vw, 620px);
            max-width: none;
            margin: 0.5rem auto;
        }

        #newSupplierModal .modal-content {
            max-height: calc(100dvh - 1rem);
        }

        #newSupplierModal .modal-header,
        #newSupplierModal .modal-footer {
            padding: 0.65rem 0.85rem;
        }

        #newSupplierModal .modal-title {
            font-size: 1.1rem;
        }

        #newSupplierModal .modal-body {
            padding: 0.75rem 0.85rem;
            overflow-y: auto;
        }

        #newSupplierModal .modal-body .mb-3 {
            margin-bottom: 0.65rem !important;
        }

        #newSupplierModal .form-label,
        #newSupplierModal .form-control,
        #newSupplierModal .btn {
            font-size: 0.9rem;
        }

        #newSupplierModal .form-control {
            min-height: 38px;
            padding: 0.35rem 0.6rem;
        }

        #newSupplierModal textarea.form-control {
            min-height: 58px;
        }

        #newSupplierModal .modal-footer .btn {
            padding: 0.35rem 0.65rem;
        }
    }

    @media (max-width: 575.98px) {
        #newSupplierModal .modal-dialog {
            width: calc(100vw - 1rem);
        }

        #newSupplierModal .modal-title {
            font-size: 1rem;
        }

        #newSupplierModal .form-label,
        #newSupplierModal .form-control,
        #newSupplierModal .btn {
            font-size: 0.85rem;
        }

        #newProductModal .modal-dialog {
            width: calc(100vw - 1rem);
            max-width: none;
            margin: 0.5rem auto;
        }

        #newProductModal .modal-content {
            max-height: calc(100dvh - 1rem);
        }

        #newProductModal .modal-header,
        #newProductModal .modal-footer {
            padding: 0.55rem 0.75rem;
        }

        #newProductModal .modal-title {
            font-size: 1rem;
        }

        #newProductModal .modal-body {
            padding: 0.65rem 0.75rem;
            overflow-y: auto;
        }

        #newProductModal .modal-body .mb-3 {
            margin-bottom: 0.55rem !important;
        }

        #newProductModal .form-label,
        #newProductModal .form-control,
        #newProductModal .form-select,
        #newProductModal .btn,
        #newProductModal small {
            font-size: 0.82rem;
        }

        #newProductModal .form-control,
        #newProductModal .form-select {
            min-height: 36px;
            padding: 0.3rem 0.55rem;
        }

        #newProductModal .modal-footer .btn {
            padding: 0.3rem 0.55rem;
        }
    }

    /* Prevent autocomplete from showing */
    input[list]::-webkit-calendar-picker-indicator {
        display: none;
    }
</style>
@endpush
@endsection
