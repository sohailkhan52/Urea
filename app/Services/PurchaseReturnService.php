<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\StockMovement;
use App\Models\SupplierLedger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Purchase Return Service
 * 
 * Handles all business logic for supplier returns.
 */
class PurchaseReturnService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Create a new purchase return (draft)
     * 
     * @param Purchase $purchase
     * @param array $data
     * @return PurchaseReturn
     * @throws \Exception
     */
    public function createReturn(Purchase $purchase, array $data): PurchaseReturn
    {
        if (!$purchase->isConfirmed()) {
            throw new \Exception('Only confirmed purchases can be returned.');
        }

        if ($purchase->isCancelled()) {
            throw new \Exception('Cancelled purchases cannot be returned.');
        }

        return DB::transaction(function () use ($purchase, $data) {
            $return = PurchaseReturn::create([
                'return_number' => $this->generateReturnNumber(),
                'purchase_id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'warehouse_id' => $purchase->warehouse_id,
                'return_date' => $data['return_date'],
                'return_type' => $data['return_type'] ?? PurchaseReturn::RETURN_TYPE_PARTIAL_ITEMS,
                'status' => PurchaseReturn::STATUS_DRAFT,
                'payment_status' => PurchaseReturn::PAYMENT_STATUS_PENDING,
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            Log::info('Purchase return created', [
                'return_id' => $return->id,
                'return_number' => $return->return_number,
                'purchase_id' => $purchase->id,
                'return_type' => $return->return_type,
                'created_by' => Auth::id(),
            ]);

            return $return;
        });
    }

    /**
     * Add item to purchase return
     * 
     * @param PurchaseReturn $return
     * @param PurchaseItem $originalItem
     * @param float $quantity
     * @param string|null $reason
     * @return PurchaseReturnItem
     * @throws \Exception
     */
    public function addItem(
        PurchaseReturn $return,
        PurchaseItem $originalItem,
        float $quantity,
        ?string $reason = null
    ): PurchaseReturnItem {
        if (!$return->isDraft()) {
            throw new \Exception('Can only add items to draft returns.');
        }

        // Validate quantity
        $remainingReturnable = $this->getRemainingReturnableQuantity($originalItem);
        
        if ($quantity > $remainingReturnable) {
            throw new \Exception(
                "Cannot return {$quantity} units. Only {$remainingReturnable} units remaining returnable " .
                "(original: {$originalItem->quantity}, already returned: " . 
                ($originalItem->quantity - $remainingReturnable) . ")."
            );
        }

        return DB::transaction(function () use ($return, $originalItem, $quantity, $reason) {
            $item = PurchaseReturnItem::create([
                'purchase_return_id' => $return->id,
                'purchase_item_id' => $originalItem->id,
                'product_id' => $originalItem->product_id,
                'quantity' => $quantity,
                'unit_price' => $originalItem->unit_price,
                'reason' => $reason,
            ]);

            // Recalculate return totals
            $this->recalculateReturnTotals($return);

            return $item;
        });
    }

    /**
     * Add all remaining items from purchase for whole order return
     * 
     * @param PurchaseReturn $return
     * @param Purchase $purchase
     * @return array
     * @throws \Exception
     */
    public function addAllRemainingItems(PurchaseReturn $return, Purchase $purchase): array
    {
        if (!$return->isDraft()) {
            throw new \Exception('Can only add items to draft returns.');
        }

        $itemsAdded = [];
        
        return DB::transaction(function () use ($return, $purchase, &$itemsAdded) {
            foreach ($purchase->items as $originalItem) {
                $remainingQty = $this->getRemainingReturnableQuantity($originalItem);
                
                // Only add items that have remaining quantity
                if ($remainingQty > 0) {
                    $item = PurchaseReturnItem::create([
                        'purchase_return_id' => $return->id,
                        'purchase_item_id' => $originalItem->id,
                        'product_id' => $originalItem->product_id,
                        'quantity' => $remainingQty,
                        'unit_price' => $originalItem->unit_price,
                        'reason' => 'Whole order return',
                    ]);
                    
                    $itemsAdded[] = $item;
                }
            }

            // Recalculate return totals
            $this->recalculateReturnTotals($return);

            return $itemsAdded;
        });
    }

    /**
     * Confirm purchase return
     * 
     * @param PurchaseReturn $return
     * @param array $refundData
     * @return PurchaseReturn
     * @throws \Exception
     */
    public function confirmReturn(PurchaseReturn $return, array $refundData = []): PurchaseReturn
    {
        if (!$return->canBeConfirmed()) {
            throw new \Exception('This return cannot be confirmed.');
        }

        return DB::transaction(function () use ($return, $refundData) {
            $purchase = $return->purchase;

            // Lock original purchase and items
            Purchase::where('id', $purchase->id)->lockForUpdate()->first();
            $originalItemIds = $return->items->pluck('purchase_item_id')->toArray();
            PurchaseItem::whereIn('id', $originalItemIds)->lockForUpdate()->get();

            // Validate all items still have sufficient returnable quantity
            foreach ($return->items as $item) {
                $remaining = $this->getRemainingReturnableQuantity($item->purchaseItem);
                if ($item->quantity > $remaining) {
                    throw new \Exception(
                        "Validation failed: Product {$item->product->name} only has {$remaining} " .
                        "units available for return."
                    );
                }
            }

            // Validate warehouse has sufficient stock for return
            foreach ($return->items as $item) {
                $currentStock = $this->stockService->getCurrentStock(
                    $return->warehouse_id,
                    $item->product_id
                );

                if ($currentStock < $item->quantity) {
                    throw new \Exception(
                        "Insufficient stock for return. Product {$item->product->name}: " .
                        "Required: {$item->quantity}, Available: {$currentStock}"
                    );
                }
            }

            // Create stock movements (remove stock from warehouse)
            foreach ($return->items as $item) {
                $this->stockService->removeStock(
                    warehouseId: $return->warehouse_id,
                    productId: $item->product_id,
                    quantity: $item->quantity,
                    type: StockMovement::TYPE_SUPPLIER_RETURN,
                    referenceType: PurchaseReturn::class,
                    referenceId: $return->id,
                    unitCost: $item->unit_price,
                    remarks: "Purchase Return #{$return->return_number} against PO #{$purchase->purchase_number}",
                    userId: Auth::id()
                );
            }

            // Calculate financial adjustments
            $this->processFinancialAdjustments($return, $purchase, $refundData);

            // Update return status
            $return->update([
                'status' => PurchaseReturn::STATUS_CONFIRMED,
                'confirmed_at' => now(),
                'confirmed_by' => Auth::id(),
            ]);

            $return->refresh();

            Log::info('Purchase return confirmed', [
                'return_id' => $return->id,
                'return_number' => $return->return_number,
                'purchase_id' => $purchase->id,
                'total_amount' => $return->total_amount,
                'confirmed_by' => Auth::id(),
            ]);

            return $return;
        }, attempts: 3);
    }

    /**
     * Cancel purchase return
     * 
     * @param PurchaseReturn $return
     * @param string $reason
     * @return PurchaseReturn
     * @throws \Exception
     */
    public function cancelReturn(PurchaseReturn $return, string $reason = ''): PurchaseReturn
    {
        if (!$return->canBeCancelled()) {
            throw new \Exception('Only confirmed returns can be cancelled.');
        }

        return DB::transaction(function () use ($return, $reason) {
            // Reverse stock movements (add stock back)
            $stockMovements = StockMovement::where('reference_type', PurchaseReturn::class)
                ->where('reference_id', $return->id)
                ->where('type', StockMovement::TYPE_SUPPLIER_RETURN)
                ->get();

            foreach ($stockMovements as $movement) {
                $this->stockService->addStock(
                    warehouseId: $movement->warehouse_id,
                    productId: $movement->product_id,
                    quantity: $movement->quantity_out,
                    type: StockMovement::TYPE_PURCHASE, // Reverse the return
                    referenceType: PurchaseReturn::class,
                    referenceId: $return->id,
                    unitCost: $movement->unit_cost,
                    remarks: "Cancelled Purchase Return #{$return->return_number}. Reason: {$reason}",
                    userId: Auth::id()
                );
            }

            // Reverse ledger entries
            $this->reverseLedgerEntries($return);

            // Update return status
            $return->update([
                'status' => PurchaseReturn::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancelled_by' => Auth::id(),
                'notes' => $return->notes . "\n\nCancellation reason: " . $reason,
            ]);

            Log::info('Purchase return cancelled', [
                'return_id' => $return->id,
                'return_number' => $return->return_number,
                'reason' => $reason,
                'cancelled_by' => Auth::id(),
            ]);

            return $return;
        });
    }

    /**
     * Get already returned quantity for a purchase item
     * 
     * @param int $purchaseItemId
     * @return float
     */
    public function getReturnedQuantity(int $purchaseItemId): float
    {
        return (float) PurchaseReturnItem::whereHas('purchaseReturn', function ($query) {
            $query->where('status', PurchaseReturn::STATUS_CONFIRMED);
        })->where('purchase_item_id', $purchaseItemId)->sum('quantity');
    }

    /**
     * Get remaining returnable quantity for a purchase item
     * 
     * @param PurchaseItem $item
     * @return float
     */
    public function getRemainingReturnableQuantity(PurchaseItem $item): float
    {
        $alreadyReturned = $this->getReturnedQuantity($item->id);
        return max(0, $item->quantity - $alreadyReturned);
    }

    /**
     * Process financial adjustments for return
     * 
     * @param PurchaseReturn $return
     * @param Purchase $purchase
     * @param array $refundData
     * @return void
     */
    protected function processFinancialAdjustments(
        PurchaseReturn $return,
        Purchase $purchase,
        array $refundData
    ): void {
        $returnAmount = $return->total_amount;
        $currentPayable = max(0, $purchase->total_amount - $purchase->paid_amount);

        // Case 1: Purchase has outstanding payable
        if ($currentPayable > 0) {
            $amountToOffset = min($returnAmount, $currentPayable);
            
            $return->update([
                'supplier_credit_amount' => $amountToOffset,
                'refund_amount' => max(0, $returnAmount - $amountToOffset),
                'payment_status' => ($returnAmount > $amountToOffset) ? 
                    PurchaseReturn::PAYMENT_STATUS_PARTIAL : 
                    PurchaseReturn::PAYMENT_STATUS_CREDITED,
            ]);
        }
        // Case 2: Purchase fully paid - we get refund or apply credit
        else {
            $refundAmount = $refundData['refund_amount'] ?? 0;
            $creditAmount = $refundData['credit_amount'] ?? $returnAmount;

            $return->update([
                'refund_amount' => $refundAmount,
                'supplier_credit_amount' => $creditAmount,
                'refund_method' => $refundData['refund_method'] ?? null,
                'refund_reference' => $refundData['refund_reference'] ?? null,
                'payment_status' => $refundAmount > 0 ? 
                    PurchaseReturn::PAYMENT_STATUS_REFUNDED : 
                    PurchaseReturn::PAYMENT_STATUS_CREDITED,
            ]);
        }

        // Create supplier ledger entry (reduces payable)
        $this->createLedgerEntry($return);
    }

    /**
     * Create supplier ledger entry for return
     * 
     * @param PurchaseReturn $return
     * @return void
     */
    protected function createLedgerEntry(PurchaseReturn $return): void
    {
        $previousBalance = $this->getSupplierRunningBalance($return->supplier_id);
        $newBalance = max(0, $previousBalance - $return->total_amount);

        SupplierLedger::create([
            'supplier_id' => $return->supplier_id,
            'type' => SupplierLedger::TYPE_RETURN,
            'purchase_return_id' => $return->id,
            'purchase_id' => $return->purchase_id,
            'payable_added' => 0,
            'payment_made' => $return->total_amount, // Acts as credit/payment
            'balance' => $newBalance,
            'description' => "Purchase Return #{$return->return_number} against PO #{$return->purchase->purchase_number}",
            'reference_number' => $return->return_number,
            'date' => $return->return_date,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Reverse ledger entries for cancelled return
     * 
     * @param PurchaseReturn $return
     * @return void
     */
    protected function reverseLedgerEntries(PurchaseReturn $return): void
    {
        $previousBalance = $this->getSupplierRunningBalance($return->supplier_id);
        $newBalance = $previousBalance + $return->total_amount;

        SupplierLedger::create([
            'supplier_id' => $return->supplier_id,
            'type' => SupplierLedger::TYPE_ADJUSTMENT,
            'purchase_return_id' => $return->id,
            'payable_added' => $return->total_amount,
            'payment_made' => 0,
            'balance' => $newBalance,
            'description' => "Reversal of cancelled Purchase Return #{$return->return_number}",
            'reference_number' => $return->return_number,
            'date' => now()->toDateString(),
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Get supplier running balance
     * 
     * @param int $supplierId
     * @return float
     */
    protected function getSupplierRunningBalance(int $supplierId): float
    {
        $latestEntry = SupplierLedger::where('supplier_id', $supplierId)
            ->latest('date')
            ->latest('created_at')
            ->first();

        return $latestEntry ? (float) $latestEntry->balance : 0.0;
    }

    /**
     * Recalculate return totals
     * 
     * @param PurchaseReturn $return
     * @return void
     */
    protected function recalculateReturnTotals(PurchaseReturn $return): void
    {
        $subtotal = $return->items()->sum(DB::raw('(quantity * unit_price)'));
        
        $return->update([
            'subtotal' => $subtotal,
            'total_amount' => max(0, $subtotal),
        ]);
    }

    /**
     * Generate unique return number
     * 
     * @return string
     * @throws \Exception
     */
    protected function generateReturnNumber(): string
    {
        $year = now()->year;
        
        $sequence = DB::table('purchase_return_sequences')
            ->where('year', $year)
            ->lockForUpdate()
            ->first();
        
        if (!$sequence) {
            DB::table('purchase_return_sequences')->insert([
                'year' => $year,
                'next_number' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $nextNumber = 1;
        } else {
            $nextNumber = $sequence->next_number;
        }
        
        if ($nextNumber > 99999) {
            throw new \Exception("Purchase return number limit exceeded for year {$year}");
        }
        
        DB::table('purchase_return_sequences')
            ->where('year', $year)
            ->update([
                'next_number' => $nextNumber + 1,
                'updated_at' => now(),
            ]);
        
        return sprintf('PR-%d-%05d', $year, $nextNumber);
    }

    /**
     * Get return summary
     * 
     * @param PurchaseReturn $return
     * @return array
     */
    public function getReturnSummary(PurchaseReturn $return): array
    {
        return [
            'total_items' => $return->items()->count(),
            'total_quantity' => $return->items()->sum('quantity'),
            'subtotal' => $return->subtotal,
            'total_amount' => $return->total_amount,
            'refund_amount' => $return->refund_amount,
            'supplier_credit_amount' => $return->supplier_credit_amount,
            'payment_status' => $return->payment_status,
        ];
    }
}
