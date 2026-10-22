<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;

/**
 * Unit Conversion Service
 * 
 * Handles all unit conversion logic for multi-unit products.
 * Provides conversion factor snapshots for transaction items.
 */
class UnitConversionService
{
    /**
     * Get conversion data for a product and unit combination
     * 
     * Returns conversion factor and validates the unit is available for the product.
     * This creates a snapshot that should be stored on the transaction item.
     * 
     * @param int $productId
     * @param int|null $unitId If null, uses product's base unit
     * @return array ['unit_id' => int, 'conversion_factor' => float, 'unit' => Unit, 'product_unit' => ProductUnit|null]
     * @throws \Exception
     */
    /**
     * Get conversion data for a product and unit combination.
     *
     * When $productUnitId is supplied, looks up the ProductUnit by its PK directly.
     * This is the only reliable way when a product has multiple ProductUnit rows that
     * share the same unit_id (e.g., "Single" and "Base" both backed by the Piece unit).
     *
     * When $productUnitId is null (legacy path), falls back to ->where('unit_id')->first().
     *
     * @param int      $productId
     * @param int|null $unitId         FK to the units table (used for legacy fallback only)
     * @param int|null $productUnitId  PK of the exact product_units row (preferred)
     */
    public function getConversionData(int $productId, ?int $unitId = null, ?int $productUnitId = null): array
    {
        $product = Product::with('baseUnit', 'productUnits.unit')->findOrFail($productId);

        // ── PATH 1: product_unit_id provided — direct PK lookup, no ambiguity ──
        if ($productUnitId !== null) {
            $productUnit = $product->productUnits()
                ->where('id', $productUnitId)
                ->where('is_active', true)
                ->first();

            if ($productUnit) {
                return [
                    'unit_id'           => $productUnit->unit_id,
                    'conversion_factor' => (float) $productUnit->conversion_to_base,
                    'unit'              => $productUnit->unit,
                    'product_unit'      => $productUnit,
                ];
            }
            // If not found (deleted/deactivated), fall through to unit_id lookup below.
        }

        // ── PATH 2: no product_unit_id — use unit_id (legacy / base-unit path) ──

        // If no unit specified, default to base unit
        if ($unitId === null) {
            if (!$product->base_unit_id) {
                throw new \Exception("Product {$product->name} has no base unit configured.");
            }

            return [
                'unit_id'           => $product->base_unit_id,
                'conversion_factor' => 1.0,
                'unit'              => $product->baseUnit,
                'product_unit'      => $product->productUnits()
                    ->where('unit_id', $product->base_unit_id)
                    ->first(),
            ];
        }

        // Find the ProductUnit configuration by unit_id (may be ambiguous if multiple
        // packages share the same unit — callers should prefer passing $productUnitId)
        $productUnit = $product->productUnits()
            ->where('unit_id', $unitId)
            ->where('is_active', true)
            ->first();

        if (!$productUnit) {
            $unit     = Unit::find($unitId);
            $unitName = $unit ? $unit->name : "Unit ID {$unitId}";
            throw new \Exception("Unit {$unitName} is not configured or not active for product {$product->name}.");
        }

        return [
            'unit_id'           => $unitId,
            'conversion_factor' => (float) $productUnit->conversion_to_base,
            'unit'              => $productUnit->unit,
            'product_unit'      => $productUnit,
        ];
    }

    /**
     * Convert quantity to base unit
     * 
     * @param float $quantity Quantity in the selected unit
     * @param float $conversionFactor Conversion factor (1 selected unit = X base units)
     * @return float Quantity in base units
     */
    public function convertToBase(float $quantity, float $conversionFactor): float
    {
        return $quantity * $conversionFactor;
    }

    /**
     * Convert base quantity to specified unit
     * 
     * @param float $baseQuantity Quantity in base units
     * @param float $conversionFactor Conversion factor
     * @return float Quantity in the specified unit
     */
    public function convertFromBase(float $baseQuantity, float $conversionFactor): float
    {
        if ($conversionFactor <= 0) {
            throw new \Exception("Invalid conversion factor: {$conversionFactor}");
        }
        
        return $baseQuantity / $conversionFactor;
    }

    /**
     * Calculate base quantity for a transaction item.
     *
     * Pass $productUnitId whenever the exact ProductUnit is known (Purchase Create,
     * Sale Create, etc.) to guarantee the correct conversion_factor is used even when
     * multiple ProductUnits share the same unit_id.
     *
     * @param int      $productId
     * @param float    $quantity      Quantity in selected unit
     * @param int|null $unitId        FK to units table (used as legacy fallback)
     * @param int|null $productUnitId PK of the exact product_units row (preferred)
     * @return array ['unit_id' => int, 'conversion_factor' => float, 'base_quantity' => float]
     */
    public function calculateTransactionData(
        int $productId,
        float $quantity,
        ?int $unitId = null,
        ?int $productUnitId = null
    ): array {
        $conversionData = $this->getConversionData($productId, $unitId, $productUnitId);

        $baseQuantity = $this->convertToBase($quantity, $conversionData['conversion_factor']);

        return [
            'unit_id'           => $conversionData['unit_id'],
            'conversion_factor' => $conversionData['conversion_factor'],
            'base_quantity'     => $baseQuantity,
        ];
    }

    /**
     * Get effective price for a product unit
     * 
     * Returns unit-specific price if configured, otherwise falls back to product base price.
     * 
     * @param int $productId
     * @param int|null $unitId
     * @param string $priceType 'purchase' or 'sale'
     * @return float|null
     */
    public function getEffectivePrice(int $productId, ?int $unitId, string $priceType = 'sale'): ?float
    {
        $product = Product::with('productUnits')->findOrFail($productId);

        // If no unit specified or using base unit, return product's base price
        if ($unitId === null || $unitId === $product->base_unit_id) {
            return $priceType === 'purchase' ? $product->purchase_price : $product->sale_price;
        }

        // Find ProductUnit configuration
        $productUnit = $product->productUnits()->where('unit_id', $unitId)->first();

        if (!$productUnit) {
            return $priceType === 'purchase' ? $product->purchase_price : $product->sale_price;
        }

        // Return unit-specific price or fallback to product price
        if ($priceType === 'purchase') {
            return $productUnit->purchase_price ?? $product->purchase_price;
        } else {
            return $productUnit->sale_price ?? $product->sale_price;
        }
    }

    /**
     * Get available units for a product
     * 
     * @param int $productId
     * @param bool $activeOnly Only return active units
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableUnits(int $productId, bool $activeOnly = true)
    {
        $query = ProductUnit::with('unit')
            ->where('product_id', $productId);

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->ordered()->get();
    }

    /**
     * Validate returnable quantity in base units
     * 
     * Compares original base quantity with already returned base quantity.
     * 
     * @param float $originalBaseQuantity Original transaction base quantity
     * @param float $alreadyReturnedBaseQuantity Sum of base quantities already returned
     * @param float $requestedReturnQuantity Quantity user wants to return (in their selected unit)
     * @param float $returnConversionFactor Conversion factor for return unit
     * @return array ['valid' => bool, 'requested_base_quantity' => float, 'remaining_base_quantity' => float, 'message' => string|null]
     */
    public function validateReturnQuantity(
        float $originalBaseQuantity,
        float $alreadyReturnedBaseQuantity,
        float $requestedReturnQuantity,
        float $returnConversionFactor
    ): array {
        $requestedBaseQuantity = $this->convertToBase($requestedReturnQuantity, $returnConversionFactor);
        $remainingBaseQuantity = $originalBaseQuantity - $alreadyReturnedBaseQuantity;

        if ($requestedBaseQuantity > $remainingBaseQuantity) {
            return [
                'valid' => false,
                'requested_base_quantity' => $requestedBaseQuantity,
                'remaining_base_quantity' => $remainingBaseQuantity,
                'message' => "Requested return quantity ({$requestedBaseQuantity} base units) exceeds remaining returnable quantity ({$remainingBaseQuantity} base units).",
            ];
        }

        return [
            'valid' => true,
            'requested_base_quantity' => $requestedBaseQuantity,
            'remaining_base_quantity' => $remainingBaseQuantity,
            'message' => null,
        ];
    }

    /**
     * Calculate base cost price from package cost
     * 
     * When purchasing in packages (e.g., Bags), calculate the per-base-unit cost.
     * Example: 1 Bag costs Rs 5000, Bag = 50 KG, so base cost = 5000/50 = Rs 100/KG
     * 
     * @param float $packagePrice Price per package unit
     * @param float $conversionFactor Conversion factor (1 package = X base units)
     * @return float Price per base unit
     */
    public function calculateBaseCostPrice(float $packagePrice, float $conversionFactor): float
    {
        if ($conversionFactor <= 0) {
            throw new \Exception("Invalid conversion factor: {$conversionFactor}");
        }

        return $packagePrice / $conversionFactor;
    }

    /**
     * Synchronize all ProductUnit prices from a new base price.
     *
     * Business rule:
     *   entered_package_price / selected_conversion  → new_base_price
     *   new_base_price × every_package_conversion    → every_package_price
     *
     * This method:
     *   1. Updates products.purchase_price (always).
     *   2. Updates products.sale_price (only when $baseSalePrice is not null).
     *   3. Recalculates purchase_price for EVERY active ProductUnit.
     *   4. Recalculates sale_price for EVERY active ProductUnit (only when $baseSalePrice is not null).
     *
     * Rules preserved:
     *   - Historical purchase_items / sale_items are NEVER touched.
     *   - Stock conversion logic is NEVER touched.
     *   - Purchase totals are NEVER recalculated here.
     *   - Cost and sale are calculated independently.
     *
     * @param Product   $product        The product whose prices must be synchronized.
     * @param float     $basePurchasePrice  New base (per-base-unit) purchase price.
     * @param float|null $baseSalePrice     New base (per-base-unit) sale price.
     *                                     Pass null to leave existing sale prices untouched.
     * @return void
     */
    public function synchronizeProductUnitPrices(
        \App\Models\Product $product,
        float $basePurchasePrice,
        ?float $baseSalePrice = null
    ): void {
        // 1. Update canonical base prices on the product row.
        $productUpdateData = ['purchase_price' => $basePurchasePrice];
        if ($baseSalePrice !== null) {
            $productUpdateData['sale_price'] = $baseSalePrice;
        }
        $product->update($productUpdateData);

        // 2. Recalculate every active ProductUnit for this product.
        $productUnits = \App\Models\ProductUnit::where('product_id', $product->id)
            ->where('is_active', true)
            ->get();

        foreach ($productUnits as $pu) {
            $conversion = (float) $pu->conversion_to_base;

            // purchase_price: always recalculate
            $unitPurchasePrice = $basePurchasePrice * $conversion;

            $unitData = ['purchase_price' => $unitPurchasePrice];

            // sale_price: only recalculate when caller provided a base sale price
            if ($baseSalePrice !== null) {
                $unitData['sale_price'] = $baseSalePrice * $conversion;
            }

            $pu->update($unitData);
        }
    }

    /**
     * Synchronize ONLY sale prices for all active ProductUnits.
     *
     * Used by Sale Create on confirmation so that sale prices are kept current
     * WITHOUT ever touching purchase/cost prices.
     *
     * Rule:
     *   entered_package_sale_price / selected_conversion → new_base_sale_price
     *   new_base_sale_price × every_package_conversion   → every_package_sale_price
     *
     * This method ONLY writes:
     *   - products.sale_price
     *   - product_units.sale_price  (for every active ProductUnit)
     *
     * It NEVER touches:
     *   - products.purchase_price
     *   - product_units.purchase_price
     *   - historical sale_items / purchase_items
     *   - stock movements
     *
     * @param \App\Models\Product $product       The product to synchronize.
     * @param float               $baseSalePrice New per-base-unit sale price.
     * @return void
     */
    public function synchronizeProductUnitSalePrices(
        \App\Models\Product $product,
        float $baseSalePrice
    ): void {
        // 1. Update canonical base sale price on the product row only.
        $product->update(['sale_price' => $baseSalePrice]);

        // 2. Recalculate every active ProductUnit sale price.
        $productUnits = \App\Models\ProductUnit::where('product_id', $product->id)
            ->where('is_active', true)
            ->get();

        foreach ($productUnits as $pu) {
            $conversion = (float) $pu->conversion_to_base;
            $pu->update(['sale_price' => $baseSalePrice * $conversion]);
        }
    }

    /**
     * Get conversion display string for UI
     * 
     * Example: "1 Bag = 50.00 KG"
     * 
     * @param int $productId
     * @param int $unitId
     * @return string
     */
    public function getConversionDisplay(int $productId, int $unitId): string
    {
        try {
            $product = Product::with('baseUnit')->findOrFail($productId);
            $productUnit = ProductUnit::with('unit')->where('product_id', $productId)->where('unit_id', $unitId)->firstOrFail();

            if ($productUnit->isBaseUnit()) {
                return "Base unit";
            }

            $baseUnitAbbr = $product->baseUnit->abbreviation ?? '';
            $unitName = $productUnit->unit->name;
            $conversion = number_format($productUnit->conversion_to_base, 4);

            return "1 {$unitName} = {$conversion} {$baseUnitAbbr}";
        } catch (\Exception $e) {
            return "N/A";
        }
    }
}
