<?php

namespace App\Services;

use App\Models\CustomerLedger;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Sales Return Service
 * 
 * Handles all business logic for customer returns.
 */
class SalesReturnService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Create a new sales return (draft)
     * 
     * @param Sale $sale
     * @param array $data
     * @return SalesReturn
     * @throws \Exception
     */
    public function createReturn(Sale $sale, array $data): SalesReturn
    {
        if (!$sale->isConfirmed()) {
            throw new \Exception('Only confirmed sales can be returned.');
        }

        if ($sale->isCancelled()) {
            throw new \Exception('Cancelled sales cannot be returned.');
        }

        return DB::transaction(function () use ($sale, $data) {
            $return = SalesReturn::create([
                'return_number' => $this->generateReturnNumber(),
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'warehouse_id' => $sale->warehouse_id,
                'return_date' => $data['return_date'],
                'status' => SalesReturn::STATUS_DRAFT,
                'payment_status' => SalesReturn::PAYMENT_STATUS_PENDING,
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            Log::info('Sales return created', [
                'return_id' => $return->id,
                'return_number' => $return->return_number,
                'sale_id' => $sale->id,
                'created_by' => Auth::id(),
            ]);

            return $return;
        });
    }

    /**
     * Add item to sales return
     * 
     * @param SalesReturn $return
     * @param SaleItem $originalItem
     * @param float $quantity
     * @param string|null $reason
     * @return SalesReturnItem
     * @throws \Exception
     */
    public function addItem(
        SalesReturn $return,
        SaleItem $originalItem,
        float $quantity,
        ?string $reason = null
    ): SalesReturnItem {
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
            $item = SalesReturnItem::create([
                'sales_return_id' => $return->id,
                'sale_item_id' => $originalItem->id,
                'product_id' => $originalItem->product_id,
                'quantity' => $quantity,
                'unit_price' => $originalItem->unit_price,
                'discount' => ($originalItem->discount / $originalItem->quantity) * $quantity, // Proportional discount
                'reason' => $reason,
            ]);

            // Recalculate return totals
            $this->recalculateReturnTotals($return);

            return $item;
        });
    }

    /**
     * Confirm sales return
     * 
     * @param SalesReturn $return
     * @param array $refundData
     * @return SalesReturn
     * @throws \Exception
     */
    public function confirmReturn(SalesReturn $return, array $refundData = []): SalesReturn
    {
        if (!$return->canBeConfirmed()) {
            throw new \Exception('This return cannot be confirmed.');
        }

        return DB::transaction(function () use ($return, $refundData) {
            $sale = $return->sale;

            // Lock original sale and items
            Sale::where('id', $sale->id)->lockForUpdate()->first();
            $originalItemIds = $return->items->pluck('sale_item_id')->toArray();
            SaleItem::whereIn('id', $originalItemIds)->lockForUpdate()->get();

            // Validate all items still have sufficient returnable quantity
            foreach ($return->items as $item) {
                $remaining = $this->getRemainingReturnableQuantity($item->saleItem);
                if ($item->quantity > $remaining) {
                    throw new \Exception(
                        "Validation failed: Product {$item->product->name} only has {$remaining} " .
                        "units available for return."
                    );
                }
            }

            // Create stock movements (add stock back to warehouse)
            foreach ($return->items as $item) {
                $this->stockService->addStock(
                    warehouseId: $return->warehouse_id,
                    productId: $item->product_id,
                    quantity: $item->quantity,
                    type: StockMovement::TYPE_CUSTOMER_RETURN,
                    referenceType: SalesReturn::class,
                    referenceId: $return->id,
                    unitCost: $item->unit_price,
                    remarks: "Sales Return #{$return->return_number} against Invoice #{$sale->invoice_number}",
                    userId: Auth::id()
                );
            }

            // Calculate financial adjustments
            $this->processFinancialAdjustments($return, $sale, $refundData);

            // Update return status
            $return->update([
                'status' => SalesReturn::STATUS_CONFIRMED,
                'confirmed_at' => now(),
                'confirmed_by' => Auth::id(),
            ]);

            $return->refresh();

            Log::info('Sales return confirmed', [
                'return_id' => $return->id,
                'return_number' => $return->return_number,
                'sale_id' => $sale->id,
                'total_amount' => $return->total_amount,
                'confirmed_by' => Auth::id(),
            ]);

            return $return;
        }, attempts: 3);
    }

    /**
     * Cancel sales return
     * 
     * @param SalesReturn $return
     * @param string $reason
     * @return SalesReturn
     * @throws \Exception
     */
    public function cancelReturn(SalesReturn $return, string $reason = ''): SalesReturn
    {
        if (!$return->canBeCancelled()) {
            throw new \Exception('Only confirmed returns can be cancelled.');
        }

        return DB::transaction(function () use ($return, $reason) {
            // Reverse stock movements
            $stockMovements = StockMovement::where('reference_type', SalesReturn::class)
                ->where('reference_id', $return->id)
                ->where('type', StockMovement::TYPE_CUSTOMER_RETURN)
                ->get();

            foreach ($stockMovements as $movement) {
                $this->stockService->removeStock(
                    warehouseId: $movement->warehouse_id,
                    productId: $movement->product_id,
                    quantity: $movement->quantity_in,
                    type: StockMovement::TYPE_SALE, // Reverse the return
                    referenceType: SalesReturn::class,
                    referenceId: $return->id,
                    unitCost: $movement->unit_cost,
                    remarks: "Cancelled Sales Return #{$return->return_number}. Reason: {$reason}",
                    userId: Auth::id()
                );
            }

            // Reverse ledger entries if customer exists
            if ($return->customer_id) {
                $this->reverseLedgerEntries($return);
            }

            // Update return status
            $return->update([
                'status' => SalesReturn::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancelled_by' => Auth::id(),
                'notes' => $return->notes . "\n\nCancellation reason: " . $reason,
            ]);

            Log::info('Sales return cancelled', [
                'return_id' => $return->id,
                'return_number' => $return->return_number,
                'reason' => $reason,
                'cancelled_by' => Auth::id(),
            ]);

            return $return;
        });
    }

    /**
     * Get already returned quantity for a sale item
     * 
     * @param int $saleItemId
     * @return float
     */
    public function getReturnedQuantity(int $saleItemId): float
    {
        return (float) SalesReturnItem::whereHas('salesReturn', function ($query) {
            $query->where('status', SalesReturn::STATUS_CONFIRMED);
        })->where('sale_item_id', $saleItemId)->sum('quantity');
    }

    /**
     * Get remaining returnable quantity for a sale item
     * 
     * @param SaleItem $item
     * @return float
     */
    public function getRemainingReturnableQuantity(SaleItem $item): float
    {
        $alreadyReturned = $this->getReturnedQuantity($item->id);
        return max(0, $item->quantity - $alreadyReturned);
    }

    /**
     * Process financial adjustments for return
     * 
     * @param SalesReturn $return
     * @param Sale $sale
     * @param array $refundData
     * @return void
     */
    protected function processFinancialAdjustments(
        SalesReturn $return,
        Sale $sale,
        array $refundData
    ): void {
        $returnAmount = $return->total_amount;

        // Case 1: Sale has outstanding due/udhar
        if ($sale->due_amount > 0) {
            $amountToOffset = min($returnAmount, $sale->due_amount);
            
            // Update sale's due/udhar amounts
            $newDueAmount = max(0, $sale->due_amount - $amountToOffset);
            $newUdharAmount = max(0, $newDueAmount);
            
            $sale->update([
                'due_amount' => $newDueAmount,
                'udhar_amount' => $newUdharAmount,
                'payment_status' => $this->calculatePaymentStatus($sale, $sale->paid_amount, $sale->total_amount, $newDueAmount),
            ]);

            $return->update([
                'customer_credit_amount' => $amountToOffset,
                'refund_amount' => max(0, $returnAmount - $amountToOffset),
                'payment_status' => ($returnAmount > $amountToOffset) ? 
                    SalesReturn::PAYMENT_STATUS_PARTIAL : 
                    SalesReturn::PAYMENT_STATUS_CREDITED,
            ]);
        }
        // Case 2: Sale fully paid - customer gets refund or credit
        else {
            $refundAmount = $refundData['refund_amount'] ?? 0;
            $creditAmount = $refundData['credit_amount'] ?? $returnAmount;

            $return->update([
                'refund_amount' => $refundAmount,
                'customer_credit_amount' => $creditAmount,
                'refund_method' => $refundData['refund_method'] ?? null,
                'refund_reference' => $refundData['refund_reference'] ?? null,
                'payment_status' => $refundAmount > 0 ? 
                    SalesReturn::PAYMENT_STATUS_REFUNDED : 
                    SalesReturn::PAYMENT_STATUS_CREDITED,
            ]);
        }

        // Create customer ledger entry (CREDIT)
        if ($return->customer_id) {
            $this->createLedgerEntry($return);
        }
    }

    /**
     * Create customer ledger entry for return
     * 
     * @param SalesReturn $return
     * @return void
     */
    protected function createLedgerEntry(SalesReturn $return): void
    {
        $previousBalance = $this->getCustomerRunningBalance($return->customer_id);
        $newBalance = max(0, $previousBalance - $return->total_amount);

        CustomerLedger::create([
            'customer_id' => $return->customer_id,
            'type' => CustomerLedger::TYPE_RETURN,
            'sales_return_id' => $return->id,
            'sale_id' => $return->sale_id,
            'debit' => 0,
            'credit' => $return->total_amount,
            'balance' => $newBalance,
            'description' => "Sales Return #{$return->return_number} against Invoice #{$return->sale->invoice_number}",
            'reference_number' => $return->return_number,
            'date' => $return->return_date,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Reverse ledger entries for cancelled return
     * 
     * @param SalesReturn $return
     * @return void
     */
    protected function reverseLedgerEntries(SalesReturn $return): void
    {
        $previousBalance = $this->getCustomerRunningBalance($return->customer_id);
        $newBalance = $previousBalance + $return->total_amount;

        CustomerLedger::create([
            'customer_id' => $return->customer_id,
            'type' => CustomerLedger::TYPE_ADJUSTMENT,
            'sales_return_id' => $return->id,
            'debit' => $return->total_amount,
            'credit' => 0,
            'balance' => $newBalance,
            'description' => "Reversal of cancelled Sales Return #{$return->return_number}",
            'reference_number' => $return->return_number,
            'date' => now()->toDateString(),
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Get customer running balance
     * 
     * @param int $customerId
     * @return float
     */
    protected function getCustomerRunningBalance(int $customerId): float
    {
        $latestEntry = CustomerLedger::where('customer_id', $customerId)
            ->latest('date')
            ->latest('created_at')
            ->first();

        return $latestEntry ? (float) $latestEntry->balance : 0.0;
    }

    /**
     * Calculate payment status
     * 
     * @param Sale $sale
     * @param float $paidAmount
     * @param float $totalAmount
     * @param float $dueAmount
     * @return string
     */
    protected function calculatePaymentStatus(Sale $sale, float $paidAmount, float $totalAmount, float $dueAmount): string
    {
        if ($dueAmount <= 0) {
            return Sale::PAYMENT_STATUS_PAID;
        } elseif ($paidAmount == 0) {
            return Sale::PAYMENT_STATUS_UNPAID;
        } else {
            return Sale::PAYMENT_STATUS_PARTIAL;
        }
    }

    /**
     * Recalculate return totals
     * 
     * @param SalesReturn $return
     * @return void
     */
    protected function recalculateReturnTotals(SalesReturn $return): void
    {
        $subtotal = $return->items()->sum(DB::raw('(quantity * unit_price)'));
        $totalDiscount = $return->items()->sum('discount');
        $totalAmount = $subtotal - $totalDiscount;

        $return->update([
            'subtotal' => $subtotal,
            'discount_adjustment' => $totalDiscount,
            'total_amount' => max(0, $totalAmount),
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
        
        $sequence = DB::table('sales_return_sequences')
            ->where('year', $year)
            ->lockForUpdate()
            ->first();
        
        if (!$sequence) {
            DB::table('sales_return_sequences')->insert([
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
            throw new \Exception("Sales return number limit exceeded for year {$year}");
        }
        
        DB::table('sales_return_sequences')
            ->where('year', $year)
            ->update([
                'next_number' => $nextNumber + 1,
                'updated_at' => now(),
            ]);
        
        return sprintf('SR-%d-%05d', $year, $nextNumber);
    }

    /**
     * Get return summary
     * 
     * @param SalesReturn $return
     * @return array
     */
    public function getReturnSummary(SalesReturn $return): array
    {
        return [
            'total_items' => $return->items()->count(),
            'total_quantity' => $return->items()->sum('quantity'),
            'subtotal' => $return->subtotal,
            'discount_adjustment' => $return->discount_adjustment,
            'total_amount' => $return->total_amount,
            'refund_amount' => $return->refund_amount,
            'customer_credit_amount' => $return->customer_credit_amount,
            'payment_status' => $return->payment_status,
        ];
    }
}
