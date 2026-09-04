<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchasePayment;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\PayableHistoryService;

/**
 * Purchase Service - Handles purchase operations and inventory integration
 * 
 * This service manages:
 * - Purchase creation and updates (draft status only)
 * - Purchase confirmation with inventory integration
 * - Purchase cancellation
 * - Stock movement creation on confirmation
 */
class PurchaseService
{
    protected StockService $stockService;
    protected PayableHistoryService $historyService;

    public function __construct(StockService $stockService, PayableHistoryService $historyService)
    {
        $this->stockService = $stockService;
        $this->historyService = $historyService;
    }

    /**
     * Create a new purchase (as draft)
     * 
     * @param array $data
     * @return Purchase
     * @throws \Exception
     */
    public function createPurchase(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            // Generate unique purchase number
            $purchaseNumber = $this->generatePurchaseNumber();

            $purchase = Purchase::create([
                'purchase_number' => $purchaseNumber,
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'purchase_date' => $data['purchase_date'] ?? now()->toDateString(),
                'status' => Purchase::STATUS_DRAFT,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            Log::info('Purchase created', [
                'purchase_id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'created_by' => Auth::id(),
            ]);

            return $purchase;
        });
    }

    /**
     * Add item to purchase (draft only)
     * 
     * @param Purchase $purchase
     * @param int $productId
     * @param float $quantity
     * @param float $unitPrice
     * @return PurchaseItem
     * @throws \Exception
     */
    public function addItem(Purchase $purchase, int $productId, float $quantity, float $unitPrice): PurchaseItem
    {
        if (!$purchase->canBeEdited()) {
            throw new \Exception("Cannot add items to non-draft purchase.");
        }

        return DB::transaction(function () use ($purchase, $productId, $quantity, $unitPrice) {
            // Debug logging
            \Log::info('addItem called', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'unit_price_type' => gettype($unitPrice),
            ]);
            
            // Check if product already exists in purchase
            $existingItem = $purchase->items()->where('product_id', $productId)->first();

            if ($existingItem) {
                throw new \Exception("Product already exists in this purchase. Please update quantity instead.");
            }

            $item = $purchase->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $quantity * $unitPrice,
            ]);

            // Verify what was stored
            \Log::info('Purchase item created', [
                'item_id' => $item->id,
                'stored_unit_price' => $item->unit_price,
                'stored_quantity' => $item->quantity,
                'stored_total' => $item->total,
            ]);

            // Recalculate purchase totals
            $this->recalculatePurchaseTotals($purchase);

            Log::info('Purchase item added', [
                'purchase_id' => $purchase->id,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);

            return $item;
        });
    }

    /**
     * Update purchase item (draft only)
     * 
     * @param PurchaseItem $item
     * @param float $quantity
     * @param float $unitPrice
     * @return PurchaseItem
     * @throws \Exception
     */
    public function updateItem(PurchaseItem $item, float $quantity, float $unitPrice): PurchaseItem
    {
        if (!$item->purchase->canBeEdited()) {
            throw new \Exception("Cannot update items in non-draft purchase.");
        }

        return DB::transaction(function () use ($item, $quantity, $unitPrice) {
            $item->update([
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $quantity * $unitPrice,
            ]);

            $this->recalculatePurchaseTotals($item->purchase);

            Log::info('Purchase item updated', [
                'item_id' => $item->id,
                'purchase_id' => $item->purchase_id,
                'quantity' => $quantity,
            ]);

            return $item;
        });
    }

    /**
     * Remove item from purchase (draft only)
     * 
     * @param PurchaseItem $item
     * @throws \Exception
     */
    public function removeItem(PurchaseItem $item): void
    {
        if (!$item->purchase->canBeEdited()) {
            throw new \Exception("Cannot remove items from non-draft purchase.");
        }

        DB::transaction(function () use ($item) {
            $purchase = $item->purchase;
            $item->delete();

            $this->recalculatePurchaseTotals($purchase);

            Log::info('Purchase item removed', [
                'purchase_id' => $purchase->id,
                'product_id' => $item->product_id,
            ]);
        });
    }

    /**
     * Update purchase expenses (draft only)
     * 
     * @param Purchase $purchase
     * @param array $data
     * @return Purchase
     * @throws \Exception
     */
    public function updateExpenses(Purchase $purchase, array $data): Purchase
    {
        if (!$purchase->canBeEdited()) {
            throw new \Exception("Cannot update expenses on non-draft purchase.");
        }

        return DB::transaction(function () use ($purchase, $data) {
            $purchase->update([
                'discount' => $data['discount'] ?? 0,
                'transport_cost' => $data['transport_cost'] ?? 0,
                'other_expenses' => $data['other_expenses'] ?? 0,
            ]);

            $this->recalculatePurchaseTotals($purchase);

            Log::info('Purchase expenses updated', [
                'purchase_id' => $purchase->id,
                'discount' => $purchase->discount,
                'transport_cost' => $purchase->transport_cost,
            ]);

            return $purchase;
        });
    }

    /**
     * Confirm purchase and create stock movements
     * 
     * CRITICAL: This operation:
     * 1. Locks purchase to draft status only
     * 2. Creates stock movements for each item
     * 3. Updates warehouse inventory
     * 4. Changes status to confirmed
     * 5. Records timestamp and user
     * 
     * @param Purchase $purchase
     * @return Purchase
     * @throws \Exception
     */
    public function confirmPurchase(Purchase $purchase, float $amountPaid = 0, ?array $itemsData = null): Purchase
    {
        if (!$purchase->canBeConfirmed()) {
            throw new \Exception("This purchase cannot be confirmed. It must be in draft status and have items.");
        }

        return DB::transaction(function () use ($purchase, $amountPaid, $itemsData) {
            // Update product prices with the purchase prices before adding stock
            foreach ($purchase->items as $item) {
                try {
                    // Reload product fresh to avoid any stale cache
                    $product = Product::find($item->product_id);
                    if (!$product) {
                        Log::warning('Product not found for purchase item', [
                            'product_id' => $item->product_id,
                            'purchase_id' => $purchase->id,
                        ]);
                        continue;
                    }
                    
                    // Prepare update data
                    $updateData = [
                        'purchase_price' => floatval($item->unit_price),
                    ];
                    
                    // If itemsData provided, check for sale_price updates
                    if ($itemsData && is_array($itemsData)) {
                        $itemData = collect($itemsData)->firstWhere('product_id', $item->product_id);
                        if ($itemData && isset($itemData['sale_price']) && floatval($itemData['sale_price']) > 0) {
                            $updateData['sale_price'] = floatval($itemData['sale_price']);
                        }
                    }
                    
                    // Update product
                    $product->update($updateData);
                    
                    Log::info('Product price updated on purchase confirmation', [
                        'product_id' => $item->product_id,
                        'product_name' => $product->name,
                        'old_purchase_price' => $product->getOriginal('purchase_price'),
                        'new_purchase_price' => $item->unit_price,
                        'sale_price_updated' => isset($updateData['sale_price']),
                        'purchase_id' => $purchase->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to update product price', [
                        'product_id' => $item->product_id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            // Add stock for each item
            foreach ($purchase->items as $item) {
                try {
                    $this->stockService->addStock(
                        warehouseId: $purchase->warehouse_id,
                        productId: $item->product_id,
                        quantity: $item->quantity,
                        type: StockMovement::TYPE_PURCHASE,
                        referenceType: Purchase::class,
                        referenceId: $purchase->id,
                        unitCost: $item->unit_price,
                        remarks: "Purchase Order #{$purchase->purchase_number}",
                        userId: Auth::id()
                    );
                } catch (\Exception $e) {
                    Log::error('Stock movement creation failed', [
                        'purchase_id' => $purchase->id,
                        'product_id' => $item->product_id,
                        'error' => $e->getMessage(),
                    ]);
                    throw new \Exception("Failed to create stock movement for {$item->product->name}: {$e->getMessage()}");
                }
            }

            // Update paid amount based on input
            $purchase->update(['paid_amount' => $amountPaid]);

            // Calculate outstanding payable and payment status
            $payableAmount = $purchase->total_amount - $amountPaid;

            if ($amountPaid >= $purchase->total_amount) {
                $paymentStatus = Purchase::PAYMENT_STATUS_PAID;
            } elseif ($amountPaid == 0) {
                $paymentStatus = Purchase::PAYMENT_STATUS_UNPAID;
            } else {
                $paymentStatus = Purchase::PAYMENT_STATUS_PARTIAL;
            }

            // Create payment record if amount paid is greater than 0
            if ($amountPaid > 0) {
                try {
                    PurchasePayment::create([
                        'payment_number' => 'PP-' . date('YmdHis') . '-' . str_pad(round(microtime(true) * 10000) % 10000, 4, '0', STR_PAD_LEFT),
                        'supplier_id' => $purchase->supplier_id,
                        'purchase_id' => $purchase->id,
                        'amount' => $amountPaid,
                        'payment_method' => PurchasePayment::METHOD_OTHER,
                        'payment_date' => now(),
                        'notes' => "Payment recorded during purchase confirmation (PO #{$purchase->purchase_number})",
                        'recorded_by' => Auth::id(),
                    ]);

                    Log::info('Purchase payment recorded', [
                        'purchase_id' => $purchase->id,
                        'amount' => $amountPaid,
                        'recorded_by' => Auth::id(),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create purchase payment', [
                        'purchase_id' => $purchase->id,
                        'error' => $e->getMessage(),
                    ]);
                    throw new \Exception("Failed to record payment: {$e->getMessage()}");
                }
            }

            // Update purchase status to CONFIRMED
            $purchase->update([
                'status' => Purchase::STATUS_CONFIRMED,
                'payment_status' => $paymentStatus,
                'confirmed_at' => now(),
                'confirmed_by' => Auth::id(),
            ]);

            // Record purchase creation in payable history
            $this->historyService->recordPurchaseCreated($purchase, Auth::id());

            Log::warning('Purchase confirmed', [
                'purchase_id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'confirmed_by' => Auth::id(),
                'total_items' => $purchase->items()->count(),
                'total_amount' => $purchase->total_amount,
                'paid_amount' => $amountPaid,
                'payable_amount' => $payableAmount,
                'payment_status' => $paymentStatus,
            ]);

            $purchase->refresh();
            
            // Dispatch PurchaseConfirmed event to trigger notifications
            \App\Events\PurchaseConfirmed::dispatch($purchase);

            return $purchase;
        });
    }

    /**
     * Cancel purchase
     * 
     * CRITICAL: Cancelled purchases:
     * - Do NOT create stock movements
     * - Do NOT affect inventory
     * - Cannot be re-confirmed
     * 
     * @param Purchase $purchase
     * @param string $reason
     * @return Purchase
     * @throws \Exception
     */
    public function cancelPurchase(Purchase $purchase, string $reason = ''): Purchase
    {
        if (!$purchase->canBeCancelled()) {
            throw new \Exception("This purchase cannot be cancelled.");
        }

        return DB::transaction(function () use ($purchase, $reason) {
            // If confirmed, we need to reverse stock movements
            if ($purchase->isConfirmed()) {
                try {
                    // Find all stock movements related to this purchase
                    $stockMovements = StockMovement::where('reference_type', Purchase::class)
                        ->where('reference_id', $purchase->id)
                        ->where('type', StockMovement::TYPE_PURCHASE)
                        ->get();

                    // Reverse each stock movement
                    foreach ($stockMovements as $movement) {
                        try {
                            $this->stockService->removeStock(
                                warehouseId: $movement->warehouse_id,
                                productId: $movement->product_id,
                                quantity: $movement->quantity_in, // Reverse the quantity that was added
                                type: StockMovement::TYPE_SUPPLIER_RETURN,
                                referenceType: Purchase::class,
                                referenceId: $purchase->id,
                                unitCost: $movement->unit_cost,
                                remarks: "Stock reversal - Purchase Order #{$purchase->purchase_number} cancelled. Original reason: {$reason}",
                                userId: Auth::id()
                            );
                        } catch (\Exception $e) {
                            Log::error('Stock reversal failed for movement', [
                                'movement_id' => $movement->id,
                                'purchase_id' => $purchase->id,
                                'error' => $e->getMessage(),
                            ]);
                            throw new \Exception("Failed to reverse stock for {$movement->product->name}: {$e->getMessage()}");
                        }
                    }

                    Log::info('Stock movements reversed for cancelled purchase', [
                        'purchase_id' => $purchase->id,
                        'movements_count' => $stockMovements->count(),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Stock reversal process failed', [
                        'purchase_id' => $purchase->id,
                        'error' => $e->getMessage(),
                    ]);
                    throw new \Exception("Stock reversal failed: {$e->getMessage()}");
                }
            }

            // Update purchase status
            $purchase->update([
                'status' => Purchase::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'notes' => ($purchase->notes ? $purchase->notes . "\n" : "") . "Cancelled: " . $reason,
            ]);

            // Record purchase cancellation in payable history
            $this->historyService->recordPurchaseCancelled($purchase, $reason, Auth::id());

            Log::warning('Purchase cancelled', [
                'purchase_id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'cancelled_by' => Auth::id(),
                'reason' => $reason,
                'was_confirmed' => $purchase->isConfirmed(),
            ]);

            return $purchase;
        });
    }

    /**
     * Recalculate purchase totals
     * 
     * @param Purchase $purchase
     */
    protected function recalculatePurchaseTotals(Purchase $purchase): void
    {
        $subtotal = $purchase->items()->sum('total');
        
        $total = $subtotal 
            - ($purchase->discount ?? 0)
            + ($purchase->transport_cost ?? 0)
            + ($purchase->other_expenses ?? 0);

        $purchase->update([
            'subtotal' => $subtotal,
            'total_amount' => max(0, $total),
        ]);
    }

    /**
     * Generate unique purchase number
     * 
     * Format: PO-YYYY-XXXXX (e.g., PO-2024-00001)
     * 
     * @return string
     */
    protected function generatePurchaseNumber(): string
    {
        $year = now()->year;
        
        // Use pessimistic locking to prevent race conditions
        $sequence = DB::table('purchase_sequences')
            ->where('year', $year)
            ->lockForUpdate()
            ->first();
        
        if (!$sequence) {
            // If sequence doesn't exist, create it
            DB::table('purchase_sequences')->insert([
                'year' => $year,
                'next_number' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $nextNumber = 1;
        } else {
            $nextNumber = $sequence->next_number;
        }
        
        // Ensure we never exceed 99999
        if ($nextNumber > 99999) {
            throw new \Exception("Purchase number limit exceeded for year {$year}");
        }
        
        // Increment the sequence for next time
        DB::table('purchase_sequences')
            ->where('year', $year)
            ->update([
                'next_number' => $nextNumber + 1,
                'updated_at' => now(),
            ]);
        
        return sprintf('PO-%d-%05d', $year, $nextNumber);
    }

    /**
     * Get purchase summary
     * 
     * @param Purchase $purchase
     * @return array
     */
    public function getPurchaseSummary(Purchase $purchase): array
    {
        return [
            'total_items' => $purchase->items()->count(),
            'total_quantity' => $purchase->items()->sum('quantity'),
            'subtotal' => $purchase->subtotal,
            'discount' => $purchase->discount,
            'transport_cost' => $purchase->transport_cost,
            'other_expenses' => $purchase->other_expenses,
            'total_amount' => $purchase->total_amount,
            'paid_amount' => $purchase->paid_amount,
            'balance' => $purchase->balance,
            'payment_status' => $purchase->payment_status,
        ];
    }

    /**
     * Calculate payment status based on paid vs total
     * 
     * @param Purchase $purchase
     * @return string
     */
    private function calculatePaymentStatus(Purchase $purchase): string
    {
        if ($purchase->paid_amount == 0) {
            return Purchase::PAYMENT_STATUS_UNPAID;
        } elseif ($purchase->paid_amount >= $purchase->total_amount) {
            return Purchase::PAYMENT_STATUS_PAID;
        } else {
            return Purchase::PAYMENT_STATUS_PARTIAL;
        }
    }
}
