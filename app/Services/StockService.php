<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseInventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Centralized Stock Management Service
 * 
 * All stock movements MUST go through this service.
 * Direct manipulation of warehouse_inventory is prohibited.
 */
class StockService
{
    /**
     * Add stock to warehouse (Stock In)
     * 
     * @param int $warehouseId
     * @param int $productId
     * @param float $quantity
     * @param string $type Movement type (opening_stock, purchase, customer_return, etc.)
     * @param string|null $referenceType Reference model class (e.g., Purchase::class)
     * @param int|null $referenceId Reference model ID
     * @param float|null $unitCost Cost per unit
     * @param string|null $remarks Additional notes
     * @param int|null $userId User performing the action (defaults to authenticated user)
     * @return StockMovement
     * @throws \Exception
     */
    public function addStock(
        int $warehouseId,
        int $productId,
        float $quantity,
        string $type,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?float $unitCost = null,
        ?string $remarks = null,
        ?int $userId = null
    ): StockMovement {
        // Validate inputs
        $this->validateStockOperation($warehouseId, $productId, $quantity, $type);

        return DB::transaction(function () use (
            $warehouseId,
            $productId,
            $quantity,
            $type,
            $referenceType,
            $referenceId,
            $unitCost,
            $remarks,
            $userId
        ) {
            // Lock warehouse inventory row for this product to prevent race conditions
            $currentBalance = $this->getCurrentStockWithLock($warehouseId, $productId);
            
            $newBalance = $currentBalance + $quantity;

            // Create stock movement record
            $movement = StockMovement::create([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'type' => $type,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'quantity_in' => $quantity,
                'quantity_out' => 0,
                'balance_after' => $newBalance,
                'unit_cost' => $unitCost,
                'remarks' => $remarks,
                'created_by' => $userId ?? Auth::id(),
            ]);

            // Update warehouse inventory
            $this->updateWarehouseInventory($warehouseId, $productId, $newBalance);

            // Log the operation
            Log::info('Stock added', [
                'movement_id' => $movement->id,
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'type' => $type,
                'balance_after' => $newBalance,
                'user_id' => $userId ?? Auth::id(),
            ]);

            return $movement;
        });
    }

    /**
     * Remove stock from warehouse (Stock Out)
     * 
     * @param int $warehouseId
     * @param int $productId
     * @param float $quantity
     * @param string $type Movement type (sale, transfer_out, adjustment_out, etc.)
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @param float|null $unitCost
     * @param string|null $remarks
     * @param int|null $userId
     * @return StockMovement
     * @throws \Exception
     */
    public function removeStock(
        int $warehouseId,
        int $productId,
        float $quantity,
        string $type,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?float $unitCost = null,
        ?string $remarks = null,
        ?int $userId = null
    ): StockMovement {
        // Validate inputs
        $this->validateStockOperation($warehouseId, $productId, $quantity, $type);

        return DB::transaction(function () use (
            $warehouseId,
            $productId,
            $quantity,
            $type,
            $referenceType,
            $referenceId,
            $unitCost,
            $remarks,
            $userId
        ) {
            // Lock warehouse inventory row for this product
            $currentBalance = $this->getCurrentStockWithLock($warehouseId, $productId);
            
            $newBalance = $currentBalance - $quantity;

            // CRITICAL: Prevent negative stock
            if ($newBalance < 0) {
                throw new \Exception(
                    "Insufficient stock. Available: {$currentBalance}, Requested: {$quantity}"
                );
            }

            // Create stock movement record
            $movement = StockMovement::create([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'type' => $type,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'quantity_in' => 0,
                'quantity_out' => $quantity,
                'balance_after' => $newBalance,
                'unit_cost' => $unitCost,
                'remarks' => $remarks,
                'created_by' => $userId ?? Auth::id(),
            ]);

            // Update warehouse inventory
            $this->updateWarehouseInventory($warehouseId, $productId, $newBalance);

            // Log the operation
            Log::info('Stock removed', [
                'movement_id' => $movement->id,
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'type' => $type,
                'balance_after' => $newBalance,
                'user_id' => $userId ?? Auth::id(),
            ]);

            return $movement;
        });
    }

    /**
     * Transfer stock between warehouses
     * 
     * @param int $sourceWarehouseId
     * @param int $destinationWarehouseId
     * @param int $productId
     * @param float $quantity
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @param string|null $remarks
     * @param int|null $userId
     * @return array ['out' => StockMovement, 'in' => StockMovement]
     * @throws \Exception
     */
    public function transferStock(
        int $sourceWarehouseId,
        int $destinationWarehouseId,
        int $productId,
        float $quantity,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $remarks = null,
        ?int $userId = null
    ): array {
        if ($sourceWarehouseId === $destinationWarehouseId) {
            throw new \Exception('Source and destination warehouses must be different.');
        }

        return DB::transaction(function () use (
            $sourceWarehouseId,
            $destinationWarehouseId,
            $productId,
            $quantity,
            $referenceType,
            $referenceId,
            $remarks,
            $userId
        ) {
            // Remove from source warehouse
            $transferOut = $this->removeStock(
                $sourceWarehouseId,
                $productId,
                $quantity,
                StockMovement::TYPE_TRANSFER_OUT,
                $referenceType,
                $referenceId,
                null,
                $remarks,
                $userId
            );

            // Add to destination warehouse
            $transferIn = $this->addStock(
                $destinationWarehouseId,
                $productId,
                $quantity,
                StockMovement::TYPE_TRANSFER_IN,
                $referenceType,
                $referenceId,
                null,
                $remarks,
                $userId
            );

            Log::info('Stock transferred', [
                'source_warehouse_id' => $sourceWarehouseId,
                'destination_warehouse_id' => $destinationWarehouseId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'out_movement_id' => $transferOut->id,
                'in_movement_id' => $transferIn->id,
            ]);

            return [
                'out' => $transferOut,
                'in' => $transferIn,
            ];
        });
    }

    /**
     * Make a stock adjustment (in or out)
     * Requires a mandatory reason
     * 
     * @param int $warehouseId
     * @param int $productId
     * @param float $quantity Positive for adjustment in, negative for adjustment out
     * @param string $reason Mandatory reason for adjustment
     * @param int|null $userId
     * @return StockMovement
     * @throws \Exception
     */
    public function adjustStock(
        int $warehouseId,
        int $productId,
        float $quantity,
        string $reason,
        ?int $userId = null
    ): StockMovement {
        if (empty(trim($reason))) {
            throw new \Exception('A reason is required for stock adjustments.');
        }

        if ($quantity == 0) {
            throw new \Exception('Adjustment quantity cannot be zero.');
        }

        if ($quantity > 0) {
            // Adjustment in
            return $this->addStock(
                $warehouseId,
                $productId,
                $quantity,
                StockMovement::TYPE_ADJUSTMENT_IN,
                null,
                null,
                null,
                $reason,
                $userId
            );
        } else {
            // Adjustment out
            return $this->removeStock(
                $warehouseId,
                $productId,
                abs($quantity),
                StockMovement::TYPE_ADJUSTMENT_OUT,
                null,
                null,
                null,
                $reason,
                $userId
            );
        }
    }

    /**
     * Get current stock for a product in a warehouse
     * 
     * @param int $warehouseId
     * @param int $productId
     * @return float
     */
    public function getCurrentStock(int $warehouseId, int $productId): float
    {
        $inventory = WarehouseInventory::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();

        return $inventory ? (float) $inventory->quantity : 0.0;
    }

    /**
     * Get current stock with row lock (for transaction safety)
     * 
     * @param int $warehouseId
     * @param int $productId
     * @return float
     */
    protected function getCurrentStockWithLock(int $warehouseId, int $productId): float
    {
        $inventory = WarehouseInventory::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->lockForUpdate() // PostgreSQL/MySQL row-level lock
            ->first();

        return $inventory ? (float) $inventory->quantity : 0.0;
    }

    /**
     * Get total stock for a product across all warehouses
     * 
     * @param int $productId
     * @return float
     */
    public function getTotalStock(int $productId): float
    {
        return (float) WarehouseInventory::where('product_id', $productId)
            ->sum('quantity');
    }

    /**
     * Get stock by warehouse for a product
     * 
     * @param int $productId
     * @return \Illuminate\Support\Collection
     */
    public function getStockByWarehouse(int $productId): \Illuminate\Support\Collection
    {
        return WarehouseInventory::with('warehouse')
            ->where('product_id', $productId)
            ->get()
            ->map(function ($inventory) {
                return [
                    'warehouse_id' => $inventory->warehouse_id,
                    'warehouse_name' => $inventory->warehouse->name,
                    'warehouse_code' => $inventory->warehouse->code,
                    'quantity' => (float) $inventory->quantity,
                ];
            });
    }

    /**
     * Get low stock items across all warehouses
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getLowStockItems(): \Illuminate\Support\Collection
    {
        return WarehouseInventory::with(['product', 'warehouse'])
            ->get()
            ->filter(function ($inventory) {
                return $inventory->isLowStock();
            })
            ->map(function ($inventory) {
                return [
                    'warehouse_id' => $inventory->warehouse_id,
                    'warehouse_name' => $inventory->warehouse->name,
                    'product_id' => $inventory->product_id,
                    'product_name' => $inventory->product->name,
                    'product_sku' => $inventory->product->sku ?? 'N/A',
                    'current_stock' => (float) $inventory->quantity,
                    'minimum_level' => 10.0, // Fixed threshold since minimum_stock_level was removed
                ];
            });
    }

    /**
     * Get stock movement history for a product in a warehouse
     * 
     * @param int $warehouseId
     * @param int $productId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getMovementHistory(
        int $warehouseId,
        int $productId,
        int $limit = 50
    ): \Illuminate\Support\Collection {
        return StockMovement::with(['warehouse', 'product', 'creator'])
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Validate stock operation inputs
     * 
     * @param int $warehouseId
     * @param int $productId
     * @param float $quantity
     * @param string $type
     * @throws \Exception
     */
    protected function validateStockOperation(
        int $warehouseId,
        int $productId,
        float $quantity,
        string $type
    ): void {
        // Validate warehouse exists and is active
        $warehouse = Warehouse::find($warehouseId);
        if (!$warehouse) {
            throw new \Exception("Warehouse with ID {$warehouseId} not found.");
        }
        if (!$warehouse->isActive()) {
            throw new \Exception("Warehouse '{$warehouse->name}' is inactive.");
        }

        // Validate product exists
        $product = Product::find($productId);
        if (!$product) {
            throw new \Exception("Product with ID {$productId} not found.");
        }

        // Validate quantity
        if ($quantity <= 0) {
            throw new \Exception("Quantity must be greater than zero.");
        }

        // Validate movement type
        $validTypes = array_keys(StockMovement::getTypes());
        if (!in_array($type, $validTypes)) {
            throw new \Exception("Invalid movement type: {$type}");
        }
    }

    /**
     * Update or create warehouse inventory record
     * 
     * @param int $warehouseId
     * @param int $productId
     * @param float $newQuantity
     */
    protected function updateWarehouseInventory(
        int $warehouseId,
        int $productId,
        float $newQuantity
    ): void {
        WarehouseInventory::updateOrCreate(
            [
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
            ],
            [
                'quantity' => $newQuantity,
            ]
        );
    }

    /**
     * Check if sufficient stock is available
     * 
     * @param int $warehouseId
     * @param int $productId
     * @param float $requiredQuantity
     * @return bool
     */
    public function hasAvailableStock(
        int $warehouseId,
        int $productId,
        float $requiredQuantity
    ): bool {
        $currentStock = $this->getCurrentStock($warehouseId, $productId);
        return $currentStock >= $requiredQuantity;
    }

    /**
     * Calculate stock value for a product in a warehouse
     * Based on weighted average cost
     * 
     * @param int $warehouseId
     * @param int $productId
     * @return float
     */
    public function getStockValue(int $warehouseId, int $productId): float
    {
        $inventory = WarehouseInventory::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();

        if (!$inventory || $inventory->quantity <= 0) {
            return 0.0;
        }

        // Get weighted average cost from recent stock movements
        $avgCost = StockMovement::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->whereNotNull('unit_cost')
            ->where('unit_cost', '>', 0)
            ->latest()
            ->limit(10)
            ->avg('unit_cost');

        return $avgCost ? ($inventory->quantity * $avgCost) : 0.0;
    }
}
