@extends('layouts.admin')

@section('title', 'Create Product')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Create New Product</h3>
            </div>
            <div class="col-auto">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.products.store') }}" method="POST" id="productForm">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input 
                                        type="text" 
                                        class="form-control @error('name') is-invalid @enderror" 
                                        id="name" 
                                        name="name" 
                                        value="{{ old('name') }}"
                                        required>
                                    @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="base_unit_id" class="form-label">Base Unit <span class="text-danger">*</span></label>
                                    <select class="form-select @error('base_unit_id') is-invalid @enderror" id="base_unit_id" name="base_unit_id" required>
                                        <option value="">-- Select Base Unit --</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ old('base_unit_id') == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->name }} ({{ $unit->abbreviation }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block mt-1">The primary unit for inventory tracking. Cannot be changed after transactions.</small>
                                    @error('base_unit_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="purchase_price" class="form-label">Base Purchase Price (Rs.) <span class="text-danger">*</span></label>
                                    <input 
                                        type="number" 
                                        class="form-control @error('purchase_price') is-invalid @enderror" 
                                        id="purchase_price" 
                                        name="purchase_price" 
                                        value="{{ old('purchase_price') }}"
                                        min="0" 
                                        step="0.01"
                                        required>
                                    <small class="text-muted d-block mt-1">Default price per base unit</small>
                                    @error('purchase_price')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sale_price" class="form-label">Base Sale Price (Rs.) <span class="text-danger">*</span></label>
                                    <input 
                                        type="number" 
                                        class="form-control @error('sale_price') is-invalid @enderror" 
                                        id="sale_price" 
                                        name="sale_price" 
                                        value="{{ old('sale_price') }}"
                                        min="0" 
                                        step="0.01"
                                        required>
                                    <small class="text-muted d-block mt-1">Default price per base unit</small>
                                    @error('sale_price')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="minimum_stock_level" class="form-label">Minimum Stock Level</label>
                            <input 
                                type="number" 
                                class="form-control" 
                                id="minimum_stock_level" 
                                name="minimum_stock_level" 
                                value="{{ old('minimum_stock_level', 10) }}"
                                min="0" 
                                step="1"
                                placeholder="10">
                            <small class="text-muted d-block mt-1">Alert will show when stock falls below this level (in base units)</small>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">Product Units / Packaging</h5>
                        <p class="text-muted small">
                            Add packaging variants for this product. Each variant will use the base unit you selected above.
                            <br>
                            <span class="badge bg-info text-dark">
                                <i class="fas fa-info-circle"></i> Base unit is auto-selected - just enter Package Name and Conversion
                            </span>
                        </p>

                        <div id="productUnitsContainer">
                            <!-- Product units will be added here -->
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="addUnitBtn">
                            <i class="fas fa-plus"></i> Add Unit
                        </button>

                        <hr class="my-4">

                        <div class="mb-0 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Product
                            </button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="productUnitTemplate">
    <div class="card mb-2 product-unit-item">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-2 col-sm-3">
                    <label class="form-label small">Unit <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm unit-select" name="product_units[INDEX][unit_id]" required>
                        <option value="">-- Select Unit --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" data-abbr="{{ $unit->abbreviation }}">
                                {{ $unit->name }} ({{ $unit->abbreviation }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-3">
                    <label class="form-label small">Package Name</label>
                    <input type="text" class="form-control form-control-sm" 
                           name="product_units[INDEX][package_name]" 
                           maxlength="100" placeholder="e.g., Small Bag">
                    <small class="text-muted">Optional variant</small>
                </div>
                <div class="col-md-2 col-sm-2">
                    <label class="form-label small">Conversion <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-sm conversion-input" 
                           name="product_units[INDEX][conversion_to_base]" 
                           min="0.0001" step="0.0001" placeholder="1.0" required>
                    <small class="text-muted conversion-display"></small>
                </div>
                <div class="col-md-2 col-sm-2">
                    <label class="form-label small">Purchase Price</label>
                    <input type="number" class="form-control form-control-sm purchase-price-input" 
                           name="product_units[INDEX][purchase_price]" 
                           min="0" step="0.01" placeholder="Optional">
                </div>
                <div class="col-md-2 col-sm-2">
                    <label class="form-label small">Sale Price</label>
                    <input type="number" class="form-control form-control-sm sale-price-input" 
                           name="product_units[INDEX][sale_price]" 
                           min="0" step="0.01" placeholder="Optional">
                </div>
                <div class="col-md-2 col-sm-12 text-end">
                    <label class="form-label small d-block d-sm-none">Actions</label>
                    <label class="form-label small d-none d-sm-block">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-danger remove-unit-btn" style="min-width: 36px;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let unitIndex = 0;
    const container = document.getElementById('productUnitsContainer');
    const template = document.getElementById('productUnitTemplate');
    const addBtn = document.getElementById('addUnitBtn');
    const baseUnitSelect = document.getElementById('base_unit_id');

    // Add unit row
    addBtn.addEventListener('click', function() {
        const clone = template.content.cloneNode(true);
        const html = clone.querySelector('.product-unit-item').outerHTML.replace(/INDEX/g, unitIndex);
        container.insertAdjacentHTML('beforeend', html);
        
        // Attach event listeners to the new row
        const newRow = container.lastElementChild;
        
        // Auto-select base unit if one is selected
        if (baseUnitSelect.value) {
            const unitSelect = newRow.querySelector('.unit-select');
            unitSelect.value = baseUnitSelect.value;
            unitSelect.disabled = true; // Lock it to base unit
            unitSelect.style.backgroundColor = '#e9ecef'; // Gray background to show it's disabled
            unitSelect.style.cursor = 'not-allowed';
            
            // Add hidden input to submit the value since disabled fields don't submit
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = unitSelect.name;
            hiddenInput.value = baseUnitSelect.value;
            unitSelect.parentNode.appendChild(hiddenInput);
            
            // Focus on package name field for quick entry
            const packageNameInput = newRow.querySelector('input[name*="[package_name]"]');
            if (packageNameInput) {
                setTimeout(() => packageNameInput.focus(), 100);
            }
        }
        
        attachRowListeners(newRow);
        
        unitIndex++;
    });

    // Attach listeners to a row
    function attachRowListeners(row) {
        // Remove button
        const removeBtn = row.querySelector('.remove-unit-btn');
        removeBtn.addEventListener('click', function() {
            row.remove();
        });

        // Conversion display and price auto-calculation
        const unitSelect = row.querySelector('.unit-select');
        const conversionInput = row.querySelector('.conversion-input');
        const conversionDisplay = row.querySelector('.conversion-display');
        const purchasePriceInput = row.querySelector('input[name*="[purchase_price]"]');
        const salePriceInput = row.querySelector('input[name*="[sale_price]"]');
        
        // Track if user has manually edited prices
        let purchasePriceManuallyEdited = false;
        let salePriceManuallyEdited = false;
        
        // Mark as manually edited when user types
        purchasePriceInput.addEventListener('input', function() {
            purchasePriceManuallyEdited = true;
        });
        
        salePriceInput.addEventListener('input', function() {
            salePriceManuallyEdited = true;
        });

        function updateConversionDisplay() {
            const selectedUnit = unitSelect.options[unitSelect.selectedIndex];
            const unitAbbr = selectedUnit ? selectedUnit.dataset.abbr : '';
            const conversion = parseFloat(conversionInput.value) || 1;
            const baseUnit = baseUnitSelect.options[baseUnitSelect.selectedIndex];
            const baseAbbr = baseUnit ? baseUnit.text.match(/\(([^)]+)\)/)?.[1] : '';
            
            if (unitAbbr && baseAbbr) {
                conversionDisplay.textContent = `1 ${unitAbbr} = ${conversion} ${baseAbbr}`;
            }
        }
        
        function autoCalculatePrices() {
            const conversion = parseFloat(conversionInput.value);
            
            // Only auto-calculate if conversion is valid and prices haven't been manually edited
            if (!conversion || conversion <= 0) return;
            
            // Get base product prices
            const basePurchasePrice = parseFloat(document.getElementById('purchase_price')?.value) || 0;
            const baseSalePrice = parseFloat(document.getElementById('sale_price')?.value) || 0;
            
            // Auto-calculate purchase price if not manually edited
            if (!purchasePriceManuallyEdited && basePurchasePrice > 0) {
                const calculatedPurchasePrice = (basePurchasePrice * conversion).toFixed(2);
                purchasePriceInput.value = calculatedPurchasePrice;
                purchasePriceInput.placeholder = `Auto: ${calculatedPurchasePrice}`;
            }
            
            // Auto-calculate sale price if not manually edited
            if (!salePriceManuallyEdited && baseSalePrice > 0) {
                const calculatedSalePrice = (baseSalePrice * conversion).toFixed(2);
                salePriceInput.value = calculatedSalePrice;
                salePriceInput.placeholder = `Auto: ${calculatedSalePrice}`;
            }
        }

        unitSelect.addEventListener('change', updateConversionDisplay);
        conversionInput.addEventListener('input', function() {
            updateConversionDisplay();
            autoCalculatePrices();
        });
        baseUnitSelect.addEventListener('change', updateConversionDisplay);
        
        // Recalculate when base prices change
        const basePurchasePriceInput = document.getElementById('purchase_price');
        const baseSalePriceInput = document.getElementById('sale_price');
        
        if (basePurchasePriceInput) {
            basePurchasePriceInput.addEventListener('input', function() {
                if (!purchasePriceManuallyEdited) {
                    autoCalculatePrices();
                }
            });
        }
        
        if (baseSalePriceInput) {
            baseSalePriceInput.addEventListener('input', function() {
                if (!salePriceManuallyEdited) {
                    autoCalculatePrices();
                }
            });
        }
    }

    // Add base unit as first ProductUnit automatically when base unit is selected
    baseUnitSelect.addEventListener('change', function() {
        // Remove any existing base unit rows first
        const existingBaseRows = Array.from(container.querySelectorAll('.product-unit-item')).filter(row => {
            const select = row.querySelector('.unit-select');
            const conversion = row.querySelector('.conversion-input');
            return select && conversion && select.value === baseUnitSelect.value && conversion.value === '1';
        });
        
        existingBaseRows.forEach(row => row.remove());

        if (baseUnitSelect.value) {
            // Create hidden base unit row (for form submission only)
            const clone = template.content.cloneNode(true);
            const html = clone.querySelector('.product-unit-item').outerHTML.replace(/INDEX/g, unitIndex);
            container.insertAdjacentHTML('beforeend', html);
            
            const newRow = container.lastElementChild;
            const unitSelect = newRow.querySelector('.unit-select');
            const conversionInput = newRow.querySelector('.conversion-input');
            const packageNameInput = newRow.querySelector('input[name*="[package_name]"]');
            
            // Set to base unit with conversion = 1
            unitSelect.value = baseUnitSelect.value;
            conversionInput.value = '1';
            conversionInput.readOnly = true; // Base unit conversion is always 1
            
            // Hide this base unit row (it's automatic, user shouldn't see it)
            newRow.style.display = 'none';
            
            // Clear package name for base unit
            if (packageNameInput) {
                packageNameInput.value = '';
            }
            
            attachRowListeners(newRow);
            
            unitIndex++;
        }
    });
});
</script>
@endpush
