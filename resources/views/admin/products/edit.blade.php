@extends('layouts.admin')

@section('title', 'Edit Product - ' . $product->name)

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Edit Product: {{ $product->name }}</h3>
            </div>
            <div class="col-auto">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.products.update', $product) }}" method="POST" id="productForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input 
                                        type="text" 
                                        class="form-control @error('name') is-invalid @enderror" 
                                        id="name" 
                                        name="name" 
                                        value="{{ old('name', $product->name) }}"
                                        required>
                                    @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="base_unit_id" class="form-label">Base Unit <span class="text-danger">*</span></label>
                                    @if($hasConfirmedTransactions)
                                        <input type="hidden" name="base_unit_id" value="{{ $product->base_unit_id }}">
                                        <input 
                                            type="text" 
                                            class="form-control" 
                                            value="{{ $product->baseUnit ? $product->baseUnit->name . ' (' . $product->baseUnit->abbreviation . ')' : $product->unit }}" 
                                            readonly>
                                        <small class="text-danger d-block mt-1">
                                            <i class="bi bi-lock"></i> Cannot change base unit after confirmed transactions
                                        </small>
                                    @else
                                        <select class="form-select @error('base_unit_id') is-invalid @enderror" id="base_unit_id" name="base_unit_id" required>
                                            <option value="">-- Select Base Unit --</option>
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}" {{ old('base_unit_id', $product->base_unit_id) == $unit->id ? 'selected' : '' }}>
                                                    {{ $unit->name }} ({{ $unit->abbreviation }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted d-block mt-1">The primary unit for inventory tracking</small>
                                    @endif
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
                                        value="{{ old('purchase_price', rtrim(rtrim(sprintf('%.2f', $product->purchase_price), '0'), '.')) }}"
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
                                        value="{{ old('sale_price', rtrim(rtrim(sprintf('%.2f', $product->sale_price), '0'), '.')) }}"
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
                                value="{{ old('minimum_stock_level', $product->minimum_stock_level ?? 10) }}"
                                min="0" 
                                step="1"
                                placeholder="10">
                            <small class="text-muted d-block mt-1">Alert will show when stock falls below this level (in base units)</small>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">Product Units / Packaging</h5>
                        <p class="text-muted small">Configure additional units for buying and selling this product (e.g., Bags, Dozens, etc.)</p>

                        <div id="productUnitsContainer">
                            @foreach($product->productUnits as $index => $productUnit)
                                <div class="card mb-2 product-unit-item" data-unit-id="{{ $productUnit->id }}">
                                    <div class="card-body">
                                        <input type="hidden" name="product_units[{{ $index }}][id]" value="{{ $productUnit->id }}">
                                        <div class="row align-items-center">
                                            <div class="col-md-2">
                                                <label class="form-label small">Unit <span class="text-danger">*</span></label>
                                                <select class="form-select form-select-sm unit-select" name="product_units[{{ $index }}][unit_id]" required>
                                                    <option value="">-- Select Unit --</option>
                                                    @foreach($units as $unit)
                                                        <option value="{{ $unit->id }}" data-abbr="{{ $unit->abbreviation }}" {{ $productUnit->unit_id == $unit->id ? 'selected' : '' }}>
                                                            {{ $unit->name }} ({{ $unit->abbreviation }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Package Name</label>
                                                <input type="text" class="form-control form-control-sm" 
                                                       name="product_units[{{ $index }}][package_name]" 
                                                       value="{{ $productUnit->package_name }}"
                                                       maxlength="100" 
                                                       placeholder="e.g., bag, box">
                                            </div>
                                            <div class="col-md-1">
                                                <label class="form-label small">Conv. <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control form-control-sm conversion-input" 
                                                       name="product_units[{{ $index }}][conversion_to_base]" 
                                                       value="{{ rtrim(rtrim(sprintf('%.4f', $productUnit->conversion_to_base), '0'), '.') }}"
                                                       min="0.0001" step="0.0001" 
                                                       {{ $productUnit->isBaseUnit() ? 'readonly' : '' }}
                                                       required>
                                                <small class="text-muted conversion-display" style="font-size: 0.7rem;">
                                                    @if($productUnit->isBaseUnit())
                                                        Base
                                                    @else
                                                        = {{ rtrim(rtrim(sprintf('%.4f', $productUnit->conversion_to_base), '0'), '.') }} {{ $product->baseUnit->abbreviation ?? '' }}
                                                    @endif
                                                </small>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Purchase</label>
                                                <input type="number" class="form-control form-control-sm" 
                                                       name="product_units[{{ $index }}][purchase_price]" 
                                                       value="{{ $productUnit->purchase_price }}"
                                                       min="0" step="0.01" placeholder="Optional">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Sale</label>
                                                <input type="number" class="form-control form-control-sm" 
                                                       name="product_units[{{ $index }}][sale_price]" 
                                                       value="{{ $productUnit->sale_price }}"
                                                       min="0" step="0.01" placeholder="Optional">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Barcode</label>
                                                <input type="text" class="form-control form-control-sm" 
                                                       name="product_units[{{ $index }}][barcode]" 
                                                       value="{{ $productUnit->barcode }}"
                                                       maxlength="100" placeholder="Optional">
                                            </div>
                                            <div class="col-md-1 text-end">
                                                <label class="form-label small d-block">&nbsp;</label>
                                                @if(!$productUnit->isBaseUnit())
                                                    <button type="button" class="btn btn-sm btn-danger remove-unit-btn">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                    <input type="hidden" name="product_units[{{ $index }}][_delete]" value="0" class="delete-flag">
                                                @else
                                                    <span class="badge bg-primary">Base</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="addUnitBtn">
                            <i class="bi bi-plus-lg"></i> Add Unit
                        </button>

                        <hr class="my-4">

                        <div class="mb-0 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Update Product
                            </button>
                            <a href="{{ route('admin.reports.products.index') }}" class="btn btn-secondary">
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
                <div class="col-md-2">
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
                <div class="col-md-2">
                    <label class="form-label small">Package Name</label>
                    <input type="text" class="form-control form-control-sm" 
                           name="product_units[INDEX][package_name]" 
                           maxlength="100" 
                           placeholder="e.g., bag, box">
                </div>
                <div class="col-md-1">
                    <label class="form-label small">Conv. <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-sm conversion-input" 
                           name="product_units[INDEX][conversion_to_base]" 
                           min="0.0001" step="0.0001" placeholder="1.0" required>
                    <small class="text-muted conversion-display" style="font-size: 0.7rem;"></small>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Purchase</label>
                    <input type="number" class="form-control form-control-sm" 
                           name="product_units[INDEX][purchase_price]" 
                           min="0" step="0.01" placeholder="Optional">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Sale</label>
                    <input type="number" class="form-control form-control-sm" 
                           name="product_units[INDEX][sale_price]" 
                           min="0" step="0.01" placeholder="Optional">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Barcode</label>
                    <input type="text" class="form-control form-control-sm" 
                           name="product_units[INDEX][barcode]" 
                           maxlength="100" placeholder="Optional">
                </div>
                <div class="col-md-1 text-end">
                    <label class="form-label small d-block">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-danger remove-unit-btn">
                        <i class="bi bi-trash"></i>
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
    let unitIndex = {{ $product->productUnits->count() }};
    const container = document.getElementById('productUnitsContainer');
    const template = document.getElementById('productUnitTemplate');
    const addBtn = document.getElementById('addUnitBtn');
    const baseUnitSelect = document.getElementById('base_unit_id');
    const hasConfirmedTransactions = {{ $hasConfirmedTransactions ? 'true' : 'false' }};

    // Add unit row
    addBtn.addEventListener('click', function() {
        const clone = template.content.cloneNode(true);
        const html = clone.querySelector('.product-unit-item').outerHTML.replace(/INDEX/g, unitIndex);
        container.insertAdjacentHTML('beforeend', html);
        
        // Attach event listeners to the new row
        const newRow = container.lastElementChild;
        attachRowListeners(newRow);
        
        unitIndex++;
    });

    // Attach listeners to existing rows
    document.querySelectorAll('.product-unit-item').forEach(row => {
        attachRowListeners(row);
    });

    // Attach listeners to a row
    function attachRowListeners(row) {
        // Remove button - mark for deletion instead of removing from DOM
        const removeBtn = row.querySelector('.remove-unit-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                const deleteFlag = row.querySelector('.delete-flag');
                if (deleteFlag) {
                    // Mark existing record for deletion
                    deleteFlag.value = '1';
                    row.style.opacity = '0.5';
                    row.style.textDecoration = 'line-through';
                    removeBtn.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i>';
                    removeBtn.classList.remove('btn-danger');
                    removeBtn.classList.add('btn-warning');
                    
                    // Change button to restore
                    removeBtn.onclick = function() {
                        deleteFlag.value = '0';
                        row.style.opacity = '1';
                        row.style.textDecoration = 'none';
                        removeBtn.innerHTML = '<i class="bi bi-trash"></i>';
                        removeBtn.classList.remove('btn-warning');
                        removeBtn.classList.add('btn-danger');
                        attachRowListeners(row);
                    };
                } else {
                    // Remove new row entirely
                    row.remove();
                }
            });
        }

        // Conversion display and price auto-calculation
        const unitSelect = row.querySelector('.unit-select');
        const conversionInput = row.querySelector('.conversion-input');
        const conversionDisplay = row.querySelector('.conversion-display');
        const purchasePriceInput = row.querySelector('input[name*="[purchase_price]"]');
        const salePriceInput = row.querySelector('input[name*="[sale_price]"]');
        
        // Track if user has manually edited prices
        let purchasePriceManuallyEdited = !!purchasePriceInput?.value; // If already has value, consider it edited
        let salePriceManuallyEdited = !!salePriceInput?.value;
        
        // Mark as manually edited when user types
        if (purchasePriceInput) {
            purchasePriceInput.addEventListener('input', function() {
                purchasePriceManuallyEdited = true;
            });
        }
        
        if (salePriceInput) {
            salePriceInput.addEventListener('input', function() {
                salePriceManuallyEdited = true;
            });
        }

        if (unitSelect && conversionInput && conversionDisplay && !conversionInput.readOnly) {
            function updateConversionDisplay() {
                const selectedUnit = unitSelect.options[unitSelect.selectedIndex];
                const unitAbbr = selectedUnit ? selectedUnit.dataset.abbr : '';
                const conversion = conversionInput.value || '1';
                
                let baseAbbr = '';
                if (baseUnitSelect && !hasConfirmedTransactions) {
                    const baseUnit = baseUnitSelect.options[baseUnitSelect.selectedIndex];
                    baseAbbr = baseUnit ? baseUnit.text.match(/\(([^)]+)\)/)?.[1] : '';
                } else {
                    // Get from hidden or displayed base unit
                    baseAbbr = '{{ $product->baseUnit->abbreviation ?? $product->unit }}';
                }
                
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
                if (purchasePriceInput && !purchasePriceManuallyEdited && basePurchasePrice > 0) {
                    const calculatedPurchasePrice = (basePurchasePrice * conversion).toFixed(2);
                    purchasePriceInput.value = calculatedPurchasePrice;
                    purchasePriceInput.placeholder = `Auto: ${calculatedPurchasePrice}`;
                }
                
                // Auto-calculate sale price if not manually edited
                if (salePriceInput && !salePriceManuallyEdited && baseSalePrice > 0) {
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
            if (baseUnitSelect) {
                baseUnitSelect.addEventListener('change', updateConversionDisplay);
            }
            
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
    }
});
</script>
@endpush
