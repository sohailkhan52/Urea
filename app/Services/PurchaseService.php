<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
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
    public function confirmPurchase(Purchase $purchase): Purchase
    {
        if (!$purchase->canBeConfirmed()) {
            throw new \Exception("This purchase cannot be confirmed. It must be in draft status and have items.");
        }

        return DB::transaction(function () use ($purchase) {
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
                        remarks: "Purchase Order #{$purchase->purchase_number} from {$purchase->supplier->name}",
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

            // Calculate payment status based on current paid_amount
            $paymentStatus = $this->calculatePaymentStatus($purchase);

            // Update purchase status
            $purchase->update([
                'status' => Purchase::STATUS_CONFIRMED,
                'payment_status' => $paymentStatus,
                'confirmed_at' => now(),
                'confirmed_by' => Auth::id(),
            ]);

            // Create initial ledger entry for this purchase
            $this->createSupplierLedgerEntry($purchase);

            Log::warning('Purchase confirmed', [
                'purchase_id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'confirmed_by' => Auth::id(),
                'total_items' => $purchase->items()->count(),
                'total_amount' => $purchase->total_amount,
                'paid_amount' => $purchase->paid_amount,
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

        // If already confirmed, we need to reverse stock movements (future enhancement)
        if ($purchase->isConfirmed()) {
            throw new \Exception("Cannot cancel confirmed purchases. This requires stock reversal (not yet implemented).");
        }

        return DB::transaction(function () use ($purchase, $reason) {
            $purchase->update([
                'status' => Purchase::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'notes' => ($purchase->notes ? $purchase->notes . "\n" : "") . "Cancelled: " . $reason,
            ]);

            Log::warning('Purchase cancelled', [
                'purchase_id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'cancelled_by' => Auth::id(),
                'reason' => $reason,
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
        $count = Purchase::whereYear('created_at', $year)->count() + 1;
        
        return sprintf('PO-%d-%05d', $year, $count);
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

    /**
     * Create initial supplier ledger entry when purchase is confirmed
     * 
     * @param Purchase $purchase
     * @throws \Exception
     */
    private function createSupplierLedgerEntry(Purchase $purchase): void
    {
        $suppLedgerModel = \App\Models\SupplierLedger::class;
        
        // Get previous balance for this supplier
        $previousEntry = $suppLedgerModel::where('supplier_id', $purchase->supplier_id)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $previousBalance = $previousEntry ? $previousEntry->balance : 0;
        $payableAmount = $purchase->total_amount - $purchase->paid_amount;
        $newBalance = $previousBalance + $payableAmount;

        $suppLedgerModel::create([
            'supplier_id' => $purchase->supplier_id,
            'type' => $suppLedgerModel::TYPE_PURCHASE,
            'purchase_id' => $purchase->id,
            'payable_added' => $payableAmount,
            'payment_made' => $purchase->paid_amount,
            'balance' => $newBalance,
            'description' => "Purchase {$purchase->purchase_number} - Rs. " . number_format($payableAmount, 2) . " payable",
            'reference_number' => $purchase->purchase_number,
            'date' => $purchase->purchase_date,
            'created_by' => Auth::id(),
        ]);
    }
}
