<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warehouse;
use App\Models\WarehouseInventory;
use Illuminate\Support\Facades\DB;

class SalesService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Create a new draft sale
     *
     * @param array $data
     * @return Sale
     * @throws \Exception
     */
    public function createSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $sale = Sale::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'customer_id' => $data['customer_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'sale_date' => $data['sale_date'],
                'status' => Sale::STATUS_DRAFT,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            return $sale;
        });
    }

    /**
     * Add item to sale (draft only)
     *
     * @param Sale $sale
     * @param int $productId
     * @param float $quantity
     * @param float $unitPrice
     * @param float $discount
     * @return SaleItem
     * @throws \Exception
     */
    public function addItem(Sale $sale, int $productId, float $quantity, float $unitPrice, float $discount = 0): SaleItem
    {
        if (!$sale->isDraft()) {
            throw new \Exception('Can only add items to draft sales.');
        }

        // Check stock availability
        $availableStock = $this->stockService->getCurrentStock($sale->warehouse_id, $productId);
        if ($availableStock < $quantity) {
            throw new \Exception("Only {$availableStock} units available in warehouse. Cannot sell {$quantity} units.");
        }

        return DB::transaction(function () use ($sale, $productId, $quantity, $unitPrice, $discount) {
            $item = $sale->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount' => $discount,
            ]);

            // Recalculate sale totals
            $this->recalculateSaleTotals($sale);

            return $item;
        });
    }

    /**
     * Update sale item (draft only)
     *
     * @param SaleItem $item
     * @param float $quantity
     * @param float $unitPrice
     * @param float $discount
     * @return SaleItem
     * @throws \Exception
     */
    public function updateItem(SaleItem $item, float $quantity, float $unitPrice, float $discount = 0): SaleItem
    {
        $sale = $item->sale;

        if (!$sale->isDraft()) {
            throw new \Exception('Can only modify items in draft sales.');
        }

        // Check stock availability (account for current quantity)
        $currentQuantity = $item->quantity;
        $quantityDifference = $quantity - $currentQuantity;

        if ($quantityDifference > 0) {
            $availableStock = $this->stockService->getCurrentStock($sale->warehouse_id, $item->product_id);
            if ($availableStock < $quantityDifference) {
                throw new \Exception("Only {$availableStock} additional units available. Cannot increase by {$quantityDifference} units.");
            }
        }

        return DB::transaction(function () use ($item, $quantity, $unitPrice, $discount) {
            $item->update([
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount' => $discount,
            ]);

            // Recalculate sale totals
            $this->recalculateSaleTotals($item->sale);

            return $item;
        });
    }

    /**
     * Remove item from sale (draft only)
     *
     * @param SaleItem $item
     * @return void
     * @throws \Exception
     */
    public function removeItem(SaleItem $item): void
    {
        if (!$item->sale->isDraft()) {
            throw new \Exception('Can only remove items from draft sales.');
        }

        DB::transaction(function () use ($item) {
            $sale = $item->sale;
            $item->delete();

            // Recalculate sale totals
            $this->recalculateSaleTotals($sale);
        });
    }

    /**
     * Confirm sale and reduce stock (ATOMIC operation with row locking)
     *
     * @param Sale $sale
     * @return Sale
     * @throws \Exception
     */
    public function confirmSale(Sale $sale, float $paidAmount = 0): Sale
    {
        if (!$sale->isDraft()) {
            throw new \Exception('Only draft sales can be confirmed.');
        }

        if ($sale->items()->count() === 0) {
            throw new \Exception('Cannot confirm sale without items.');
        }

        return DB::transaction(function () use ($sale, $paidAmount) {
            // Validate paid amount
            if ($paidAmount < 0) {
                throw new \Exception('Paid amount cannot be negative.');
            }

            if ($paidAmount > $sale->total_amount) {
                throw new \Exception('Paid amount cannot exceed total amount.');
            }

            // Lock the warehouse inventory rows to prevent overselling
            $inventoryRows = WarehouseInventory::where('warehouse_id', $sale->warehouse_id)
                ->lockForUpdate()
                ->get();

            // Verify stock availability for all items
            foreach ($sale->items as $item) {
                $currentStock = $this->stockService->getCurrentStock($sale->warehouse_id, $item->product_id);

                if ($currentStock < $item->quantity) {
                    throw new \Exception(
                        "Insufficient stock for {$item->product->name}. " .
                        "Required: {$item->quantity}, Available: {$currentStock}"
                    );
                }
            }

            // Create stock movements for each item
            foreach ($sale->items as $item) {
                $this->stockService->removeStock(
                    warehouseId: $sale->warehouse_id,
                    productId: $item->product_id,
                    quantity: $item->quantity,
                    type: \App\Models\StockMovement::TYPE_SALE,
                    referenceType: Sale::class,
                    referenceId: $sale->id,
                    unitCost: $item->unit_price,
                    remarks: "Sale #{$sale->invoice_number}",
                    userId: auth()->id()
                );
            }

            // Ensure paid amount is valid (between 0 and total)
            $validPaidAmount = max(0, min($paidAmount, $sale->total_amount));
            
            // Calculate due and udhar amounts
            $dueAmount = max(0, $sale->total_amount - $validPaidAmount);
            $udharAmount = max(0, $dueAmount);

            // Calculate payment status
            if ($validPaidAmount == 0) {
                $paymentStatus = Sale::PAYMENT_STATUS_UNPAID;
            } elseif ($validPaidAmount >= $sale->total_amount) {
                $paymentStatus = Sale::PAYMENT_STATUS_PAID;
            } else {
                $paymentStatus = Sale::PAYMENT_STATUS_PARTIAL;
            }

            // Update sale status and payment info
            $sale->update([
                'status' => Sale::STATUS_CONFIRMED,
                'confirmed_at' => now(),
                'confirmed_by' => auth()->id(),
                'paid_amount' => $validPaidAmount,
                'due_amount' => $dueAmount,
                'udhar_amount' => $udharAmount,
                'payment_status' => $paymentStatus,
            ]);

            // If customer exists, create ledger entry for the sale
            if ($sale->customer_id) {
                $paymentService = app(PaymentService::class);
                $paymentService->createSaleLedgerEntry($sale, auth()->id());
            }

            $sale->refresh();
            
            // Dispatch SaleConfirmed event to trigger notifications
            \App\Events\SaleConfirmed::dispatch($sale);

            return $sale;
        });
    }

    /**
     * Cancel sale (safe and reversible)
     * If already confirmed, create reverse stock movements
     *
     * @param Sale $sale
     * @param string $reason
     * @return Sale
     * @throws \Exception
     */
    public function cancelSale(Sale $sale, string $reason = ''): Sale
    {
        if ($sale->isCancelled()) {
            throw new \Exception('Sale is already cancelled.');
        }

        return DB::transaction(function () use ($sale, $reason) {
            // If confirmed, create reverse stock movements
            if ($sale->isConfirmed()) {
                foreach ($sale->items as $item) {
                    // Create reverse movement
                    $this->stockService->addStock(
                        warehouseId: $sale->warehouse_id,
                        productId: $item->product_id,
                        quantity: $item->quantity,
                        unitCost: $item->unit_price,
                        reason: "Reverse: Sale #{$sale->invoice_number} cancelled. {$reason}",
                        referenceType: 'sale_reversal',
                        referenceId: $sale->id,
                        createdBy: auth()->id()
                    );
                }
            }

            // Update sale status
            $sale->update([
                'status' => Sale::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            return $sale;
        });
    }

    /**
     * Record payment for sale
     *
     * @param Sale $sale
     * @param float $amount
     * @return Sale
     * @throws \Exception
     */
    public function recordPayment(Sale $sale, float $amount): Sale
    {
        return DB::transaction(function () use ($sale, $amount) {
            $newPaidAmount = $sale->paid_amount + $amount;

            if ($newPaidAmount > $sale->total_amount) {
                throw new \Exception('Payment cannot exceed total amount.');
            }

            $sale->update([
                'paid_amount' => $newPaidAmount,
                'due_amount' => $sale->total_amount - $newPaidAmount,
            ]);

            return $sale;
        });
    }

    /**
     * Recalculate sale totals after item changes
     *
     * @param Sale $sale
     * @return void
     */
    public function recalculateSaleTotals(Sale $sale): void
    {
        $subtotal = $sale->items()->sum(DB::raw('(quantity * unit_price)'));
        $totalDiscount = $sale->items()->sum('discount');
        $saleDiscount = $sale->discount ?? 0;

        $totalAmount = $subtotal - $totalDiscount - $saleDiscount;
        $paidAmount = $sale->paid_amount ?? 0;
        $dueAmount = max(0, $totalAmount - $paidAmount);

        $sale->update([
            'subtotal' => $subtotal,
            'total_amount' => $totalAmount,
            'due_amount' => $dueAmount,
        ]);
    }

    /**
     * Update sale-level discount
     *
     * @param Sale $sale
     * @param float $discount
     * @return Sale
     * @throws \Exception
     */
    public function updateDiscount(Sale $sale, float $discount): Sale
    {
        if (!$sale->isDraft()) {
            throw new \Exception('Can only update discount on draft sales.');
        }

        return DB::transaction(function () use ($sale, $discount) {
            $sale->update(['discount' => $discount]);

            // Recalculate totals
            $this->recalculateSaleTotals($sale);

            return $sale;
        });
    }

    /**
     * Get sale summary for display
     *
     * @param Sale $sale
     * @return array
     */
    public function getSaleSummary(Sale $sale): array
    {
        return [
            'subtotal' => $sale->subtotal,
            'discount' => $sale->discount,
            'total_amount' => $sale->total_amount,
            'paid_amount' => $sale->paid_amount,
            'due_amount' => $sale->due_amount,
            'items_count' => $sale->items()->count(),
            'total_qty' => $sale->items()->sum('quantity'),
            'payment_status' => $sale->payment_status,
        ];
    }

    /**
     * Generate unique invoice number
     *
     * @return string
     */
    protected function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $latestSale = Sale::whereYear('created_at', $year)
            ->latest('id')
            ->first();

        $nextNumber = ($latestSale ? (int)substr($latestSale->invoice_number, -5) + 1 : 1);

        return sprintf('INV-%d-%05d', $year, $nextNumber);
    }
}
