<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\CustomerPayment;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Sale Return Service
 * 
 * Handles all business logic for creating and managing sale returns.
 * Integrates with StockService for inventory updates and follows
 * existing payment/udhar patterns for financial adjustments.
 */
class SaleReturnService
{
    protected StockService $stockService;
    protected UdharHistoryService $udharHistoryService;

    public function __construct(StockService $stockService, UdharHistoryService $udharHistoryService)
    {
        $this->stockService = $stockService;
        $this->udharHistoryService = $udharHistoryService;
    }

    /**
     * Create a new sale return (draft status)
     * 
     * @param Sale $sale
     * @param array $items Array of items to return: [['sale_item_id' => int, 'quantity' => float], ...]
     * @param array $data Additional data: return_date, reason, notes
     * @return SaleReturn
     * @throws \Exception
     */
    public function createReturn(Sale $sale, array $items, array $data = []): SaleReturn
    {
        // Validate sale is confirmed
        if (!$sale->isConfirmed()) {
            throw new \Exception('Cannot create return for non-confirmed sale.');
        }

        // Validate items
        if (empty($items)) {
            throw new \Exception('No items provided for return.');
        }

        // Validate return items and quantities
        $this->validateReturnItems($sale, $items);

        return DB::transaction(function () use ($sale, $items, $data) {
            // Create sale return
            $return = SaleReturn::create([
                'return_number' => SaleReturn::generateReturnNumber(),
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'family_id' => $sale->family_id, // Automatically carry over from sale
                'warehouse_id' => $sale->warehouse_id,
                'return_date' => $data['return_date'] ?? now(),
                'total_return_amount' => 0, // Will be calculated from items
                'status' => SaleReturn::STATUS_DRAFT,
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Create return items
            $totalReturnAmount = 0;
            foreach ($items as $itemData) {
                $saleItem = SaleItem::findOrFail($itemData['sale_item_id']);
                
                // Validate item belongs to this sale
                if ($saleItem->sale_id != $sale->id) {
                    throw new \Exception("Sale item {$saleItem->id} does not belong to sale {$sale->id}.");
                }

                $returnQty = (float) $itemData['quantity'];
                $returnTotal = $returnQty * $saleItem->unit_price;
                
                SaleReturnItem::create([
                    'sale_return_id' => $return->id,
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id,
                    'quantity' => $returnQty,
                    'unit_price' => $saleItem->unit_price, // Use original sale price
                    'total' => $returnTotal,
                ]);

                $totalReturnAmount += $returnTotal;
            }

            // Update total return amount
            $return->total_return_amount = $totalReturnAmount;
            $return->save();

            Log::info('Sale return created', [
                'return_id' => $return->id,
                'return_number' => $return->return_number,
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'family_id' => $sale->family_id,
                'total_return_amount' => $totalReturnAmount,
                'created_by' => Auth::id(),
            ]);

            return $return->fresh(['items', 'sale', 'customer', 'family']);
        });
    }

    /**
     * Confirm a sale return
     * This will:
     * 1. Add stock back to warehouse
     * 2. Adjust customer balance (reduce udhar or create credit)
     * 3. Update return status to confirmed
     * 
     * @param SaleReturn $return
     * @return SaleReturn
     * @throws \Exception
     */
    public function confirmReturn(SaleReturn $return): SaleReturn
    {
        if (!$return->canBeConfirmed()) {
            throw new \Exception('Return cannot be confirmed. Current status: ' . $return->status);
        }

        return DB::transaction(function () use ($return) {
            $sale = $return->sale;

            // 1. Add stock back to warehouse for each returned item
            foreach ($return->items as $returnItem) {
                $this->stockService->addStock(
                    $return->warehouse_id,
                    $returnItem->product_id,
                    $returnItem->quantity,
                    StockMovement::TYPE_CUSTOMER_RETURN,
                    SaleReturn::class,
                    $return->id,
                    $returnItem->unit_price,
                    "Sale Return {$return->return_number} - Customer returned items",
                    Auth::id()
                );
            }

            // 2. Adjust customer balance
            $this->adjustCustomerBalance($sale, $return);

            // 3. Update return status
            $return->update([
                'status' => SaleReturn::STATUS_CONFIRMED,
                'confirmed_at' => now(),
                'confirmed_by' => Auth::id(),
            ]);

            Log::info('Sale return confirmed', [
                'return_id' => $return->id,
                'return_number' => $return->return_number,
                'sale_id' => $sale->id,
                'total_return_amount' => $return->total_return_amount,
                'confirmed_by' => Auth::id(),
            ]);

            return $return->fresh(['items', 'sale', 'customer']);
        });
    }

    /**
     * Cancel a draft return
     * 
     * @param SaleReturn $return
     * @param string|null $reason
     * @return SaleReturn
     * @throws \Exception
     */
    public function cancelReturn(SaleReturn $return, ?string $reason = null): SaleReturn
    {
        if (!$return->canBeCancelled()) {
            throw new \Exception('Return cannot be cancelled. Current status: ' . $return->status);
        }

        $return->update([
            'status' => SaleReturn::STATUS_CANCELLED,
            'notes' => $return->notes . ($reason ? "\n\nCancellation reason: " . $reason : ''),
        ]);

        Log::info('Sale return cancelled', [
            'return_id' => $return->id,
            'return_number' => $return->return_number,
            'reason' => $reason,
        ]);

        return $return->fresh();
    }

    /**
     * Get return summary for a sale
     * Shows what can still be returned for each sale item
     * 
     * @param Sale $sale
     * @return array
     */
    public function getSaleReturnSummary(Sale $sale): array
    {
        $items = $sale->items()->with(['product'])->get();
        
        $summary = [];
        foreach ($items as $item) {
            $totalReturned = $item->total_returned_quantity;
            $returnableQty = $item->returnable_quantity;
            
            $summary[] = [
                'sale_item_id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_sku' => $item->product->sku ?? 'N/A',
                'original_quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_returned' => $totalReturned,
                'returnable_quantity' => $returnableQty,
                'can_be_returned' => $returnableQty > 0,
            ];
        }
        
        return $summary;
    }

    /**
     * Get all returns for a sale
     * 
     * @param int $saleId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSaleReturns(int $saleId)
    {
        return SaleReturn::with(['items.product', 'creator', 'confirmer'])
            ->where('sale_id', $saleId)
            ->orderBy('return_date', 'desc')
            ->get();
    }

    /**
     * Get all returns for a customer
     * 
     * @param int $customerId
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCustomerReturns(int $customerId, array $filters = [])
    {
        $query = SaleReturn::with(['sale', 'items.product', 'warehouse'])
            ->where('customer_id', $customerId);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('return_date', [$filters['start_date'], $filters['end_date']]);
        }

        return $query->orderBy('return_date', 'desc')->get();
    }

    /**
     * Validate return items before creating return
     * 
     * @param Sale $sale
     * @param array $items
     * @throws \Exception
     */
    protected function validateReturnItems(Sale $sale, array $items): void
    {
        foreach ($items as $itemData) {
            // Validate required fields
            if (!isset($itemData['sale_item_id']) || !isset($itemData['quantity'])) {
                throw new \Exception('Invalid return item data. Both sale_item_id and quantity are required.');
            }

            // Get sale item
            $saleItem = SaleItem::find($itemData['sale_item_id']);
            if (!$saleItem || $saleItem->sale_id != $sale->id) {
                throw new \Exception('Invalid sale item.');
            }

            // Validate quantity
            $returnQty = (float) $itemData['quantity'];
            if ($returnQty <= 0) {
                throw new \Exception('Return quantity must be greater than 0.');
            }

            // Check returnable quantity
            $returnableQty = $saleItem->returnable_quantity;
            if ($returnQty > $returnableQty) {
                $productName = $saleItem->product->name;
                throw new \Exception(
                    "Cannot return {$returnQty} of {$productName}. " .
                    "Maximum returnable quantity is {$returnableQty} " .
                    "(Original: {$saleItem->quantity}, Already returned: {$saleItem->total_returned_quantity})."
                );
            }
        }
    }

    /**
     * Adjust customer balance based on return
     * 
     * This follows the existing payment/udhar pattern:
     * - If sale has outstanding udhar: create payment to reduce debt
     * - If sale is fully paid: create customer credit (negative payment for future use)
     * 
     * @param Sale $sale
     * @param SaleReturn $return
     * @throws \Exception
     */
    protected function adjustCustomerBalance(Sale $sale, SaleReturn $return): void
    {
        $returnAmount = $return->total_return_amount;
        
        // Calculate current udhar considering additional payments
        $currentUdhar = $sale->current_remaining_udhar;

        if ($currentUdhar > 0) {
            // Sale has outstanding udhar: reduce it by creating a payment
            $paymentAmount = min($returnAmount, $currentUdhar);
            
            CustomerPayment::create([
                'customer_id' => $sale->customer_id,
                'sale_id' => $sale->id,
                'amount' => $paymentAmount,
                'payment_date' => $return->return_date,
                'payment_method' => 'return_adjustment',
                'reference_number' => $return->return_number,
                'notes' => "Payment adjustment for sale return {$return->return_number}",
                'received_by' => Auth::id(),
            ]);

            Log::info('Customer udhar reduced via return', [
                'customer_id' => $sale->customer_id,
                'sale_id' => $sale->id,
                'return_id' => $return->id,
                'payment_amount' => $paymentAmount,
                'previous_udhar' => $currentUdhar,
            ]);

            // If return amount exceeds udhar, create credit for the difference
            if ($returnAmount > $currentUdhar) {
                $creditAmount = $returnAmount - $currentUdhar;
                
                CustomerPayment::create([
                    'customer_id' => $sale->customer_id,
                    'sale_id' => $sale->id, // Keep reference to original sale
                    'amount' => -$creditAmount, // Negative = customer has credit
                    'payment_date' => $return->return_date,
                    'payment_method' => 'return_credit',
                    'reference_number' => $return->return_number,
                    'notes' => "Customer credit from return {$return->return_number} (Original Sale: {$sale->invoice_number})",
                    'received_by' => Auth::id(),
                ]);

                Log::info('Customer credit created from return', [
                    'customer_id' => $sale->customer_id,
                    'return_id' => $return->id,
                    'credit_amount' => $creditAmount,
                ]);
            }
        } else {
            // Sale is fully paid: create customer credit for future use
            CustomerPayment::create([
                'customer_id' => $sale->customer_id,
                'sale_id' => $sale->id, // Keep reference to original sale
                'amount' => -$returnAmount, // Negative = customer has credit
                'payment_date' => $return->return_date,
                'payment_method' => 'return_credit',
                'reference_number' => $return->return_number,
                'notes' => "Customer credit from return {$return->return_number} (Original Sale: {$sale->invoice_number})",
                'received_by' => Auth::id(),
            ]);

            Log::info('Customer credit created from return', [
                'customer_id' => $sale->customer_id,
                'return_id' => $return->id,
                'credit_amount' => $returnAmount,
            ]);
        }

        // Record return in UdharHistory
        $previousUdhar = $sale->current_remaining_udhar;
        $currentUdhar = max(0, $previousUdhar - $returnAmount);
        
        $this->udharHistoryService->recordReturnCreated(
            $sale,
            $returnAmount,
            $previousUdhar,
            $currentUdhar,
            $return->return_number,
            Auth::id()
        );
    }

    /**
     * Calculate how much quantity can still be returned for a sale item
     * 
     * @param int $saleItemId
     * @return float
     */
    public function getRemainingReturnableQuantity(int $saleItemId): float
    {
        $saleItem = SaleItem::findOrFail($saleItemId);
        return $saleItem->returnable_quantity;
    }
}
