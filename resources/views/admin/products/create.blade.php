@extends('layouts.admin')

@section('title', 'Create Product')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Create New Product</h3>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.products.store') }}" method="POST" id="productForm">
                        @csrf

                        {{-- ── Product Name ── --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name"
                                   value="{{ old('name') }}"
                                   placeholder="e.g., Urea, Sugar, Cold Drink"
                                   required>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- ── Base Unit ── --}}
                        <div class="mb-3">
                            <label for="base_unit_id" class="form-label">Base Unit <span class="text-danger">*</span></label>
                            <select class="form-select @error('base_unit_id') is-invalid @enderror"
                                    id="base_unit_id" name="base_unit_id" required>
                                <option value="">-- Select Base Unit --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}"
                                            data-abbr="{{ $unit->abbreviation }}"
                                            {{ old('base_unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }} ({{ $unit->abbreviation }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">The primary unit for inventory. Cannot be changed after transactions.</small>
                            @error('base_unit_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- ── Minimum Stock Level ── --}}
                        <div class="mb-3">
                            <label for="minimum_stock_level" class="form-label">Minimum Stock Level</label>
                            <input type="number" class="form-control"
                                   id="minimum_stock_level" name="minimum_stock_level"
                                   value="{{ old('minimum_stock_level', 10) }}"
                                   min="0" step="1" placeholder="10">
                            <small class="text-muted d-block mt-1">Alert when stock falls below this level (in base units).</small>
                        </div>

                        <hr class="my-4">

                        {{-- ── Bag / Box Checkbox ── --}}
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       id="has_package" name="has_package" value="1"
                                       {{ old('has_package') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="has_package">
                                    This product is sold / purchased as Bag or Box
                                </label>
                            </div>
                            <small class="text-muted d-block ms-4">
                                Check this if you buy/sell by the bag or box (e.g., 1 Bag = 50 KG).
                            </small>
                        </div>

                        {{-- ══════════════════════════════════════════════════════════ --}}
                        {{-- Section A — NO package (simple unit prices)               --}}
                        {{-- ══════════════════════════════════════════════════════════ --}}
                        <div id="sectionNormal">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="purchase_price" class="form-label">
                                            Unit Cost Price (Rs.) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number"
                                               class="form-control @error('purchase_price') is-invalid @enderror"
                                               id="purchase_price" name="purchase_price"
                                               value="{{ old('purchase_price') }}"
                                               min="0" step="0.01">
                                        <small class="text-muted d-block mt-1" id="normalPurchaseHint">
                                            Cost price per base unit.
                                        </small>
                                        @error('purchase_price')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sale_price" class="form-label">
                                            Unit Sell Price (Rs.) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number"
                                               class="form-control @error('sale_price') is-invalid @enderror"
                                               id="sale_price" name="sale_price"
                                               value="{{ old('sale_price') }}"
                                               min="0" step="0.01">
                                        <small class="text-muted d-block mt-1" id="normalSaleHint">
                                            Sale price per base unit.
                                        </small>
                                        @error('sale_price')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ══════════════════════════════════════════════════════════ --}}
                        {{-- Section B — HAS package (Bag / Box)                      --}}
                        {{-- ══════════════════════════════════════════════════════════ --}}
                        <div id="sectionPackage" style="display:none;">

                            {{-- Package Type --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Package Type <span class="text-danger">*</span></label>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="package_type" id="pkgBag" value="Bag"
                                               {{ old('package_type', 'Bag') === 'Bag' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pkgBag">Bag</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="package_type" id="pkgBox" value="Box"
                                               {{ old('package_type') === 'Box' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pkgBox">Box</label>
                                    </div>
                                </div>
                                @error('package_type')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Package Quantity + Prices --}}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="package_conversion" class="form-label" id="conversionLabel">
                                            Bag/Box Quantity <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="number"
                                                   class="form-control @error('package_conversion') is-invalid @enderror"
                                                   id="package_conversion" name="package_conversion"
                                                   value="{{ old('package_conversion') }}"
                                                   min="0.0001" step="0.0001"
                                                   placeholder="e.g., 50">
                                            <span class="input-group-text" id="unitAbbrBadge">units</span>
                                        </div>
                                        <small class="text-muted d-block mt-1" id="conversionHint">
                                            How many base units fit in one package.
                                        </small>
                                        @error('package_conversion')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="pkg_purchase_price" class="form-label" id="pkgCostLabel">
                                            Bag Cost Price (Rs.) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number"
                                               class="form-control @error('pkg_purchase_price') is-invalid @enderror"
                                               id="pkg_purchase_price" name="pkg_purchase_price"
                                               value="{{ old('pkg_purchase_price') }}"
                                               min="0" step="0.01"
                                               placeholder="e.g., 4500">
                                        <small class="text-muted d-block mt-1" id="pkgCostHint">
                                            Cost for one complete Bag.
                                        </small>
                                        @error('pkg_purchase_price')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="pkg_sale_price" class="form-label" id="pkgSaleLabel">
                                            Bag Sell Price (Rs.) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number"
                                               class="form-control @error('pkg_sale_price') is-invalid @enderror"
                                               id="pkg_sale_price" name="pkg_sale_price"
                                               value="{{ old('pkg_sale_price') }}"
                                               min="0" step="0.01"
                                               placeholder="e.g., 5000">
                                        <small class="text-muted d-block mt-1" id="pkgSaleHint">
                                            Sale price for one complete Bag.
                                        </small>
                                        @error('pkg_sale_price')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Calculated base price preview --}}
                            <div id="basePricePreview" class="alert alert-info py-2 small" style="display:none;">
                                <strong>Base unit prices (auto-calculated):</strong>
                                <span id="basePriceText"></span>
                            </div>

                            {{-- Hidden fields are DISABLED when package mode is off,
                                 so they never override the visible price fields.
                                 JS enables them only when submitting with package mode ON. --}}
                            <input type="hidden" id="purchase_price_hidden" name="purchase_price"
                                   value="{{ old('purchase_price', '0') }}" disabled>
                            <input type="hidden" id="sale_price_hidden"    name="sale_price"
                                   value="{{ old('sale_price',    '0') }}" disabled>
                        </div>

                        {{-- ── Hidden product_units[] rows ──────────────────────────
                             These are submitted only when the package checkbox is ON.
                             They map directly to the existing store() product_units[] structure.
                        ──────────────────────────────────────────────────────────── --}}
                        <div id="hiddenUnitsContainer"></div>

                        <hr class="my-4">

                        <div class="mb-0 text-end">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-check-lg"></i> Create Product
                            </button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary ms-2">
                                Cancel
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Element refs ───────────────────────────────────────────────────────
    const hasPackageCb      = document.getElementById('has_package');
    const sectionNormal     = document.getElementById('sectionNormal');
    const sectionPackage    = document.getElementById('sectionPackage');

    const baseUnitSelect    = document.getElementById('base_unit_id');
    const unitAbbrBadge     = document.getElementById('unitAbbrBadge');

    const pkgBagRadio       = document.getElementById('pkgBag');
    const pkgBoxRadio       = document.getElementById('pkgBox');

    const pkgConversionInput  = document.getElementById('package_conversion');
    const pkgPurchaseInput    = document.getElementById('pkg_purchase_price');
    const pkgSaleInput        = document.getElementById('pkg_sale_price');

    // Hidden base-price fields (used when package mode is ON)
    const purchasePriceHidden = document.getElementById('purchase_price_hidden');
    const salePriceHidden     = document.getElementById('sale_price_hidden');

    // Visible base-price fields (used when package mode is OFF)
    const purchasePriceVisible = document.getElementById('purchase_price');
    const salePriceVisible     = document.getElementById('sale_price');

    const hiddenUnitsContainer = document.getElementById('hiddenUnitsContainer');

    const basePricePreview  = document.getElementById('basePricePreview');
    const basePriceText     = document.getElementById('basePriceText');

    // Label elements
    const conversionLabel   = document.getElementById('conversionLabel');
    const pkgCostLabel      = document.getElementById('pkgCostLabel');
    const pkgSaleLabel      = document.getElementById('pkgSaleLabel');
    const pkgCostHint       = document.getElementById('pkgCostHint');
    const pkgSaleHint       = document.getElementById('pkgSaleHint');
    const normalPurchaseHint = document.getElementById('normalPurchaseHint');
    const normalSaleHint     = document.getElementById('normalSaleHint');

    // ── Helpers ────────────────────────────────────────────────────────────

    function selectedBaseAbbr() {
        const opt = baseUnitSelect.options[baseUnitSelect.selectedIndex];
        return opt ? opt.dataset.abbr || opt.text : 'units';
    }

    function selectedPackageType() {
        return pkgBagRadio.checked ? 'Bag' : 'Box';
    }

    function updateLabels() {
        const abbr     = selectedBaseAbbr();
        const pkgType  = selectedPackageType();
        const baseUnitName = baseUnitSelect.options[baseUnitSelect.selectedIndex]?.text?.replace(/\s*\(.*\)/, '').trim() || 'unit';

        // Unit abbreviation badge next to conversion input
        unitAbbrBadge.textContent = abbr;

        // Dynamic label for conversion field
        conversionLabel.innerHTML = `${pkgType} Quantity / Weight <span class="text-danger">*</span>`;

        // Dynamic labels for price fields
        pkgCostLabel.innerHTML = `${pkgType} Cost Price (Rs.) <span class="text-danger">*</span>`;
        pkgSaleLabel.innerHTML = `${pkgType} Sell Price (Rs.) <span class="text-danger">*</span>`;
        pkgCostHint.textContent = `Cost for one complete ${pkgType}.`;
        pkgSaleHint.textContent = `Sale price for one complete ${pkgType}.`;

        // Normal mode hints
        normalPurchaseHint.textContent = abbr !== 'units'
            ? `Cost price per 1 ${abbr}.`
            : 'Cost price per base unit.';
        normalSaleHint.textContent = abbr !== 'units'
            ? `Sale price per 1 ${abbr}.`
            : 'Sale price per base unit.';
    }

    function updateBasePreview() {
        const conversion = parseFloat(pkgConversionInput.value);
        const pkgCost    = parseFloat(pkgPurchaseInput.value);
        const pkgSale    = parseFloat(pkgSaleInput.value);

        if (conversion > 0 && (pkgCost > 0 || pkgSale > 0)) {
            const abbr    = selectedBaseAbbr();
            const pkgType = selectedPackageType();
            const parts   = [];

            if (pkgCost > 0) {
                const base = pkgCost / conversion;
                parts.push(`Cost per ${abbr}: Rs. ${base.toFixed(2)}`);
                purchasePriceHidden.value = base.toFixed(4);
            }
            if (pkgSale > 0) {
                const base = pkgSale / conversion;
                parts.push(`Sale per ${abbr}: Rs. ${base.toFixed(2)}`);
                salePriceHidden.value = base.toFixed(4);
            }

            basePriceText.textContent = ' ' + parts.join(' | ');
            basePricePreview.style.display = '';
        } else {
            basePricePreview.style.display = 'none';
        }
    }

    function buildHiddenProductUnits() {
        // Clear existing
        hiddenUnitsContainer.innerHTML = '';

        if (!hasPackageCb.checked || !baseUnitSelect.value) {
            return;
        }

        const baseUnitId  = baseUnitSelect.value;
        const pkgType     = selectedPackageType();
        const conversion  = pkgConversionInput.value || '0';
        const pkgCost     = pkgPurchaseInput.value   || '';
        const pkgSale     = pkgSaleInput.value        || '';

        // ── Row 0: base unit ProductUnit (NULL package_name, conversion 1) ──
        hiddenUnitsContainer.innerHTML += `
            <input type="hidden" name="product_units[0][unit_id]"          value="${baseUnitId}">
            <input type="hidden" name="product_units[0][package_name]"     value="">
            <input type="hidden" name="product_units[0][conversion_to_base]" value="1">
            <input type="hidden" name="product_units[0][purchase_price]"   value="${purchasePriceHidden.value}">
            <input type="hidden" name="product_units[0][sale_price]"       value="${salePriceHidden.value}">
        `;

        // ── Row 1: the Bag / Box ProductUnit ──
        hiddenUnitsContainer.innerHTML += `
            <input type="hidden" name="product_units[1][unit_id]"          value="${baseUnitId}">
            <input type="hidden" name="product_units[1][package_name]"     value="${pkgType}">
            <input type="hidden" name="product_units[1][conversion_to_base]" value="${conversion}">
            <input type="hidden" name="product_units[1][purchase_price]"   value="${pkgCost}">
            <input type="hidden" name="product_units[1][sale_price]"       value="${pkgSale}">
        `;
    }

    // ── Toggle between Normal and Package sections ─────────────────────────

    function applyMode() {
        const isPackage = hasPackageCb.checked;

        sectionNormal.style.display  = isPackage ? 'none' : '';
        sectionPackage.style.display = isPackage ? ''     : 'none';

        // Required attributes for visible fields
        purchasePriceVisible.required = !isPackage;
        salePriceVisible.required     = !isPackage;
        pkgConversionInput.required   = isPackage;
        pkgPurchaseInput.required     = isPackage;
        pkgSaleInput.required         = isPackage;

        // Enable hidden base-price fields ONLY when package mode is ON,
        // so they don't submit competing values when package mode is OFF.
        purchasePriceHidden.disabled = !isPackage;
        salePriceHidden.disabled     = !isPackage;

        // When switching back to normal, clear the hidden units
        if (!isPackage) {
            hiddenUnitsContainer.innerHTML = '';
        }

        updateLabels();
    }

    // ── Event listeners ────────────────────────────────────────────────────

    hasPackageCb.addEventListener('change', applyMode);

    baseUnitSelect.addEventListener('change', function () {
        updateLabels();
        updateBasePreview();
    });

    [pkgBagRadio, pkgBoxRadio].forEach(r => r.addEventListener('change', function () {
        updateLabels();
    }));

    [pkgConversionInput, pkgPurchaseInput, pkgSaleInput].forEach(el => {
        el.addEventListener('input', function () {
            updateBasePreview();
        });
    });

    // ── Form submit ────────────────────────────────────────────────────────

    document.getElementById('productForm').addEventListener('submit', function (e) {
        if (hasPackageCb.checked) {
            // Recalculate hidden base prices and build product_units[] inputs
            updateBasePreview();
            buildHiddenProductUnits();

            // Enable hidden fields so they submit
            purchasePriceHidden.disabled = false;
            salePriceHidden.disabled     = false;

            // Guard: conversion must be > 0
            const conversion = parseFloat(pkgConversionInput.value);
            if (!conversion || conversion <= 0) {
                e.preventDefault();
                pkgConversionInput.focus();
                pkgConversionInput.classList.add('is-invalid');
                return;
            }

            // Guard: prices must be filled
            if (!parseFloat(pkgPurchaseInput.value) || !parseFloat(pkgSaleInput.value)) {
                e.preventDefault();
                (!parseFloat(pkgPurchaseInput.value) ? pkgPurchaseInput : pkgSaleInput).focus();
                return;
            }
        }
    });

    // ── Initialise ─────────────────────────────────────────────────────────
    applyMode();  // apply based on old() values (for validation error re-display)
    updateLabels();
});
</script>
@endpush
