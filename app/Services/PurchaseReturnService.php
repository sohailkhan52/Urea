<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\SupplierLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\UnitConversionService;

/**
 * Purchase Return Service
 * 
 * Multi-unit support:
 * - Validates returnable quantities using base units
 * - Stores unit conversion snapshots on return items
 * - Uses base quantities for stock movements
 */
class PurchaseReturnService
{
    protected StockService $stockService;
    protected PayableHistoryService $payableHistoryService;
    protected UnitConversionService $conversionService;

    public function __construct(
        StockService $stockService, 
        PayableHistoryService $payableHistoryService,
        UnitConversionService $conversionService
    ) {
        $this->stockService = $stockService;
        $this->payableHistoryService = $payableHistoryService;
        $this->conversionService = $conversionService;
    }

    /**
     * Create a new purchase return
     */
    public function createReturn(
        int $purchaseId,
        array $items,
        string $returnDate,
        int $createdBy,
        ?string $reason = null,
        ?string $notes = null
    ): PurchaseReturn {
        return DB::transaction(function () use (
            $purchaseId,
            $items,
            $returnDate,
            $createdBy,
            $reason,
            $notes
        ) {
            // Load the original purchase
            $purchase = Purchase::with(['items.product.baseUnit'])->findOrFail($purchaseId);

            // Validate purchase is confirmed
            if (!$purchase->isConfirmed()) {
                throw new \Exception('Cannot return items from an unconfirmed purchase.');
            }

            // Validate return quantities
            $this->validateReturnQuantities($items, $purchase);

            // Generate return number
            $returnNumber = $this->generateReturnNumber();

            // Calculate subtotal (only for items with quantity > 0)
            $subtotal = collect($items)
                ->filter(function ($item) {
                    return $item['quantity'] > 0;
                })
                ->sum(function ($item) use ($purchase) {
                    $purchaseItem = $purchase->items->firstWhere('id', $item['purchase_item_id']);

                    return $item['quantity'] * $purchaseItem->unit_price;
                });

            // Create purchase return
            $return = PurchaseReturn::create([
                'return_number' => $returnNumber,
                'purchase_id' => $purchaseId,
                'supplier_id' => $purchase->supplier_id,
                'warehouse_id' => $purchase->warehouse_id,
                'return_date' => $returnDate,
                'status' => PurchaseReturn::STATUS_DRAFT,
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'refund_amount' => 0,
                'refund_status' => PurchaseReturn::REFUND_STATUS_PENDING,
                'reason' => $reason,
                'notes' => $notes,
                'created_by' => $createdBy,
            ]);

            // Create return items (only for items with quantity > 0)
            foreach ($items as $itemData) {
                // Skip items with 0 quantity
                if ($itemData['quantity'] <= 0) {
                    continue;
                }

                $purchaseItem = $purchase->items->firstWhere('id', $itemData['purchase_item_id']);
                $purchaseUnitData = $this->getPurchaseUnitData($purchaseItem);
                $baseQuantity = (float) $itemData['quantity'] * $purchaseUnitData['conversion_factor'];

                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'purchase_item_id' => $itemData['purchase_item_id'],
                    'product_id' => $purchaseItem->product_id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $purchaseItem->unit_price,
                    'unit_id' => $purchaseUnitData['unit_id'],
                    'conversion_factor' => $purchaseUnitData['conversion_factor'],
                    'base_quantity' => $baseQuantity,
                ]);
            }

            return $return->fresh(['items', 'purchase', 'supplier', 'warehouse']);
        });
    }

    /**
     * Confirm a purchase return
     */
    public function confirmReturn(PurchaseReturn $return, int $confirmedBy): PurchaseReturn
    {
        return DB::transaction(function () use ($return, $confirmedBy) {
            if (!$return->isDraft()) {
                throw new \Exception('Only draft returns can be confirmed.');
            }

            // Reduce stock for each returned item using BASE QUANTITIES
            foreach ($return->items as $item) {
                $baseQuantity = $item->base_quantity ?? $item->quantity;
                $unitCost = $baseQuantity > 0
                    ? (float) $item->total / $baseQuantity
                    : (float) $item->unit_price;
                
                $this->stockService->removeStock(
                    $return->warehouse_id,
                    $item->product_id,
                    $baseQuantity,
                    StockMovement::TYPE_SUPPLIER_RETURN,
                    PurchaseReturn::class,
                    $return->id,
                    $unitCost,
                    "Purchase Return: {$return->return_number}",
                    $confirmedBy
                );
            }

            // Create supplier ledger entry for the return (credit the supplier)
            $this->createReturnLedgerEntry($return, $confirmedBy);

            // Determine refund status based on supplier's outstanding balance
            $refundStatus = $this->determineRefundStatus($return);

            // Update return status and refund status
            $return->update([
                'status' => PurchaseReturn::STATUS_CONFIRMED,
                'refund_status' => $refundStatus,
                'confirmed_at' => now(),
                'confirmed_by' => $confirmedBy,
            ]);

            Log::info('Purchase return confirmed', [
                'return_id' => $return->id,
                'return_number' => $return->return_number,
                'supplier_id' => $return->supplier_id,
                'total_amount' => $return->total_amount,
                'refund_status' => $refundStatus,
                'confirmed_by' => $confirmedBy,
            ]);

            return $return->fresh();
        });
    }

    /**
     * Cancel a purchase return
     */
    public function cancelReturn(PurchaseReturn $return): PurchaseReturn
    {
        return DB::transaction(function () use ($return) {
            if ($return->isConfirmed()) {
                throw new \Exception('Cannot cancel a confirmed return. Please create a new purchase to adjust stock.');
            }

            $return->update([
                'status' => PurchaseReturn::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            return $return->fresh();
        });
    }

    /**
     * Get total returned quantity for a purchase item (in BASE UNITS)
     * 
     * This sums up base_quantity from all confirmed returns.
     * For legacy returns without base_quantity, falls back to quantity.
     */
    public function getReturnedQuantity(int $purchaseItemId): float
    {
        return PurchaseReturnItem::whereHas('purchaseReturn', function ($query) {
            $query->where('status', PurchaseReturn::STATUS_CONFIRMED);
        })
        ->where('purchase_item_id', $purchaseItemId)
        ->get()
        ->sum(function ($item) {
            // Use base_quantity if available, otherwise fall back to quantity
            return $item->base_quantity ?? $item->quantity;
        });
    }

    /**
     * Get remaining returnable quantity for a purchase item (in BASE UNITS)
     */
    public function getRemainingReturnableQuantity(PurchaseItem $purchaseItem): float
    {
        $originalBaseQty = $purchaseItem->base_quantity ?? $purchaseItem->quantity;
        $returnedBaseQty = $this->getReturnedQuantity($purchaseItem->id);
        return max(0, $originalBaseQty - $returnedBaseQty);
    }

    /**
     * Validate return quantities against purchase items using BASE UNITS
     * 
     * CRITICAL: Must compare base quantities to prevent returning more than purchased.
     * Example: Purchased 10 Bags (500 KG), can't return 11 Bags (550 KG).
     */
    protected function validateReturnQuantities(array $items, Purchase $purchase): void
    {
        // Filter out items with 0 quantity first
        $itemsToReturn = array_filter($items, function($item) {
            return $item['quantity'] > 0;
        });

        // At least one item must have quantity > 0
        if (empty($itemsToReturn)) {
            throw new \Exception('Please select at least one item with quantity greater than zero to return.');
        }

        foreach ($itemsToReturn as $itemData) {
            $purchaseItem = $purchase->items->firstWhere('id', $itemData['purchase_item_id']);

            if (!$purchaseItem) {
                throw new \Exception("Invalid purchase item ID: {$itemData['purchase_item_id']}");
            }

            // Use the conversion snapshot saved on the original purchase item.
            $purchaseUnitData = $this->getPurchaseUnitData($purchaseItem);
            $returnBaseQuantity = (float) $itemData['quantity'] * $purchaseUnitData['conversion_factor'];

            // Get original base quantity and already returned base quantity
            $originalBaseQty = $purchaseItem->base_quantity ?? $purchaseItem->quantity;
            $returnedBaseQty = $this->getReturnedQuantity($purchaseItem->id);
            $remainingBaseQty = $originalBaseQty - $returnedBaseQty;

            if ($returnBaseQuantity > $remainingBaseQty) {
                $productName = $purchaseItem->product->name ?? 'Unknown';
                $unitName = $purchaseItem->unit->abbreviation
                    ?? $purchaseItem->product->baseUnit->abbreviation
                    ?? $purchaseItem->product->unit
                    ?? 'units';

                throw new \Exception(
                    "Cannot return {$itemData['quantity']} {$unitName} of {$productName}. " .
                    "Remaining returnable: " . ($remainingBaseQty / $purchaseUnitData['conversion_factor']) . " {$unitName} " .
                    "(original: {$originalBaseQty}, already returned: {$returnedBaseQty})"
                );
            }
        }
    }

    /**
     * Get the transaction unit and conversion factor recorded at purchase time.
     *
     * Older purchase items without a saved factor fall back to the current unit
     * configuration, while new purchase returns always use the saved snapshot.
     *
     * @return array{unit_id: int|null, conversion_factor: float}
     */
    protected function getPurchaseUnitData(PurchaseItem $purchaseItem): array
    {
        if ($purchaseItem->conversion_factor !== null && (float) $purchaseItem->conversion_factor > 0) {
            return [
                'unit_id' => $purchaseItem->unit_id ?? $purchaseItem->product?->base_unit_id,
                'conversion_factor' => (float) $purchaseItem->conversion_factor,
            ];
        }

        $conversionData = $this->conversionService->calculateTransactionData(
            $purchaseItem->product_id,
            1,
            $purchaseItem->unit_id,
            $purchaseItem->product_unit_id
        );

        return [
            'unit_id' => $conversionData['unit_id'],
            'conversion_factor' => $conversionData['conversion_factor'],
        ];
    }

    /**
     * Generate a unique return number
     */
    protected function generateReturnNumber(): string
    {
        $prefix = 'PR-';
        $date = now()->format('Ymd');
        
        // Get the last return number for today
        $lastReturn = PurchaseReturn::where('return_number', 'like', "{$prefix}{$date}-%")
            ->orderBy('return_number', 'desc')
            ->first();

        if ($lastReturn) {
            // Extract the sequence number and increment
            $lastSequence = (int) substr($lastReturn->return_number, -5);
            $sequence = str_pad($lastSequence + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $sequence = '00001';
        }

        return "{$prefix}{$date}-{$sequence}";
    }

    /**
     * Create supplier ledger entry for purchase return
     * 
     * This credits the supplier's account, reducing their payable balance
     * 
     * @param PurchaseReturn $return
     * @param int $confirmedBy
     * @throws \Exception
     */
    protected function createReturnLedgerEntry(PurchaseReturn $return, int $confirmedBy): void
    {
        // Get previous balance for this supplier
        $previousEntry = SupplierLedger::where('supplier_id', $return->supplier_id)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $previousBalance = $previousEntry ? $previousEntry->balance : 0;
        $previousPayableAmount = $previousBalance;

        // Return reduces the payable (credit to supplier)
        $newBalance = max(0, $previousBalance - $return->total_amount);
        $currentPayableAmount = $newBalance;

        SupplierLedger::create([
            'supplier_id' => $return->supplier_id,
            'type' => SupplierLedger::TYPE_RETURN,
            'purchase_id' => $return->purchase_id,
            'purchase_return_id' => $return->id,
            'payable_added' => 0, // No new payable
            'payment_made' => $return->total_amount, // Treat return as credit (reduces payable)
            'balance' => $newBalance,
            'description' => "Purchase Return {$return->return_number} - Credit for returned items",
            'reference_number' => $return->return_number,
            'date' => $return->return_date,
            'created_by' => $confirmedBy,
        ]);

        // Record in PayableHistory
        $this->payableHistoryService->recordReturnCreated(
            $return->purchase,
            $return->total_amount,
            $previousPayableAmount,
            $currentPayableAmount,
            $return->return_number,
            $return->refund_status,
            $confirmedBy
        );

        Log::info('Supplier ledger entry created for purchase return', [
            'return_id' => $return->id,
            'supplier_id' => $return->supplier_id,
            'return_amount' => $return->total_amount,
            'previous_balance' => $previousBalance,
            'new_balance' => $newBalance,
        ]);
    }

    /**
     * Determine refund status based on supplier's payment balance
     * 
     * - If supplier has NO outstanding balance: refund_status = COMPLETED (fully paid)
     * - If supplier has outstanding balance: refund_status = PARTIAL (unpaid)
     * 
     * @param PurchaseReturn $return
     * @return string
     */
    protected function determineRefundStatus(PurchaseReturn $return): string
    {
        $supplier = $return->supplier;

        // Get the latest ledger entry for this supplier to check outstanding balance
        $latestLedgerEntry = SupplierLedger::where('supplier_id', $return->supplier_id)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $outstandingBalance = $latestLedgerEntry ? (float) $latestLedgerEntry->balance : 0.0;

        // If no outstanding balance, refund is completed (fully paid)
        if ($outstandingBalance <= 0) {
            return PurchaseReturn::REFUND_STATUS_COMPLETED;
        }

        // If there is outstanding balance, refund is partial (unpaid)
        return PurchaseReturn::REFUND_STATUS_PARTIAL;
    }
}
