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

class PurchaseReturnService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
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
            $purchase = Purchase::with('items')->findOrFail($purchaseId);

            // Validate purchase is confirmed
            if (!$purchase->isConfirmed()) {
                throw new \Exception('Cannot return items from an unconfirmed purchase.');
            }

            // Validate return quantities
            $this->validateReturnQuantities($items, $purchase);

            // Generate return number
            $returnNumber = $this->generateReturnNumber();

            // Calculate subtotal
            $subtotal = collect($items)->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
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

            // Create return items
            foreach ($items as $itemData) {
                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'purchase_item_id' => $itemData['purchase_item_id'],
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
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

            // Reduce stock for each returned item
            foreach ($return->items as $item) {
                $this->stockService->removeStock(
                    $return->warehouse_id,
                    $item->product_id,
                    $item->quantity,
                    StockMovement::TYPE_SUPPLIER_RETURN,
                    PurchaseReturn::class,
                    $return->id,
                    $item->unit_price,
                    "Purchase Return: {$return->return_number}",
                    $confirmedBy
                );
            }

            // Update return status
            $return->update([
                'status' => PurchaseReturn::STATUS_CONFIRMED,
                'confirmed_at' => now(),
                'confirmed_by' => $confirmedBy,
            ]);

            // Create supplier ledger entry for the return (credit the supplier)
            $this->createReturnLedgerEntry($return, $confirmedBy);

            Log::info('Purchase return confirmed', [
                'return_id' => $return->id,
                'return_number' => $return->return_number,
                'supplier_id' => $return->supplier_id,
                'total_amount' => $return->total_amount,
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
     * Get total returned quantity for a purchase item
     */
    public function getReturnedQuantity(int $purchaseItemId): float
    {
        return PurchaseReturnItem::whereHas('purchaseReturn', function ($query) {
            $query->where('status', PurchaseReturn::STATUS_CONFIRMED);
        })
        ->where('purchase_item_id', $purchaseItemId)
        ->sum('quantity');
    }

    /**
     * Get remaining returnable quantity for a purchase item
     */
    public function getRemainingReturnableQuantity(PurchaseItem $purchaseItem): float
    {
        $returnedQty = $this->getReturnedQuantity($purchaseItem->id);
        return max(0, $purchaseItem->quantity - $returnedQty);
    }

    /**
     * Validate return quantities against purchase items
     */
    protected function validateReturnQuantities(array $items, Purchase $purchase): void
    {
        foreach ($items as $itemData) {
            $purchaseItem = $purchase->items->find($itemData['purchase_item_id']);

            if (!$purchaseItem) {
                throw new \Exception("Invalid purchase item ID: {$itemData['purchase_item_id']}");
            }

            // Check remaining returnable quantity
            $returnedQty = $this->getReturnedQuantity($purchaseItem->id);
            $remainingQty = $purchaseItem->quantity - $returnedQty;

            if ($itemData['quantity'] > $remainingQty) {
                $productName = $purchaseItem->product->name ?? 'Unknown';
                throw new \Exception(
                    "Cannot return {$itemData['quantity']} of {$productName}. " .
                    "Only {$remainingQty} remaining (already returned: {$returnedQty})"
                );
            }
        }
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

        // Return reduces the payable (credit to supplier)
        $newBalance = max(0, $previousBalance - $return->total_amount);

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

        Log::info('Supplier ledger entry created for purchase return', [
            'return_id' => $return->id,
            'supplier_id' => $return->supplier_id,
            'return_amount' => $return->total_amount,
            'previous_balance' => $previousBalance,
            'new_balance' => $newBalance,
        ]);
    }
}
