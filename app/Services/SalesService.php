<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseInventory;
use Illuminate\Support\Facades\DB;
use App\Services\UnitConversionService;

/**
 * Sales Service - Handles sale operations and inventory integration
 * 
 * Multi-unit support:
 * - Products can be sold in different units (KG, Bags, etc.)
 * - Stock availability checked using base quantities
 * - Cost price and profit calculations use base units
 */
class SalesService
{
    protected StockService $stockService;
    protected UnitConversionService $conversionService;

    public function __construct(StockService $stockService, UnitConversionService $conversionService)
    {
        $this->stockService = $stockService;
        $this->conversionService = $conversionService;
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
            // Determine udhar_account_type based on family_id
            $udharAccountType = !empty($data['family_id']) 
                ? Sale::UDHAR_ACCOUNT_TYPE_FAMILY 
                : Sale::UDHAR_ACCOUNT_TYPE_INDIVIDUAL;

            $sale = Sale::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'customer_id' => $data['customer_id'] ?? null,
                'family_id' => $data['family_id'] ?? null,
                'udhar_account_type' => $udharAccountType,
                'walkin_customer_name' => $data['walkin_customer_name'] ?? null,
                'walkin_customer_contact' => $data['walkin_customer_contact'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'sale_date' => $data['sale_date'],
                'discount' => $data['discount'] ?? 0,
                'status' => Sale::STATUS_DRAFT,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            return $sale;
        });
    }

    /**
     * Create sale with items in one transaction (for single-page sale form)
     *
     * @param array $data
     * @return Sale
     * @throws \Exception
     */
    public function createSaleWithItems(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            // Validate customer is provided
            if (empty($data['customer_id'])) {
                throw new \Exception('Customer is required for sale.');
            }

            $customer = Customer::findOrFail($data['customer_id']);

            // If a family is provided for the sale, update the customer's family
            if (!empty($data['family_id'])) {
                $customer->update(['family_id' => $data['family_id']]);
            }

            // Determine udhar_account_type based on family_id
            $udharAccountType = !empty($data['family_id']) 
                ? Sale::UDHAR_ACCOUNT_TYPE_FAMILY 
                : Sale::UDHAR_ACCOUNT_TYPE_INDIVIDUAL;

            // Create the sale
            $sale = Sale::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'customer_id' => $data['customer_id'],
                'family_id' => $data['family_id'] ?? null,
                'udhar_account_type' => $udharAccountType,
                'warehouse_id' => $data['warehouse_id'],
                'sale_date' => $data['sale_date'] ?? now()->toDateString(),
                'discount' => $data['discount'] ?? 0,
                'paid_amount' => $data['paid_amount'] ?? 0,
                'status' => Sale::STATUS_DRAFT,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);;

            // Add items if provided
            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    if (empty($itemData['product_id']) || empty($itemData['quantity']) || empty($itemData['unit_price'])) {
                        continue; // Skip invalid items
                    }

                    // Multi-unit support: get conversion data
                    // Pass product_unit_id (PK) for unambiguous lookup when multiple
                    // packages share the same unit_id (e.g. Carton, Box, Bag all backed by Piece).
                    $unitId        = $itemData['unit_id'] ?? null;
                    $productUnitId = $itemData['product_unit_id'] ?? null;

                    $conversionData = $this->conversionService->calculateTransactionData(
                        $itemData['product_id'],
                        $itemData['quantity'],
                        $unitId,
                        $productUnitId  // exact ProductUnit PK — eliminates ambiguity
                    );

                    // Check stock availability using base quantity
                    $availableStock = $this->stockService->getCurrentStock($sale->warehouse_id, $itemData['product_id']);
                    if ($availableStock < $conversionData['base_quantity']) {
                        $product     = Product::find($itemData['product_id']);
                        $unitName    = 'units';
                        if ($productUnitId) {
                            $pu = \App\Models\ProductUnit::with('unit')->find($productUnitId);
                            $unitName = $pu ? ($pu->package_name ?: $pu->unit->name) : 'units';
                        } elseif ($unitId) {
                            $pu = \App\Models\ProductUnit::with('unit')->find($unitId);
                            $unitName = $pu ? $pu->unit->name : 'units';
                        }
                        throw new \Exception("Insufficient stock for {$product->name}. Available: {$availableStock} (base units), Requested: {$itemData['quantity']} {$unitName} = {$conversionData['base_quantity']} (base units)");
                    }

                    // Get product's current purchase price as cost
                    $product   = Product::find($itemData['product_id']);
                    $costPrice = $product ? $product->purchase_price : 0;

                    $sale->items()->create([
                        'product_id'       => $itemData['product_id'],
                        'quantity'         => $itemData['quantity'],
                        'unit_id'          => $conversionData['unit_id'],
                        'product_unit_id'  => $productUnitId,  // store exact ProductUnit ID
                        'conversion_factor'=> $conversionData['conversion_factor'],
                        'base_quantity'    => $conversionData['base_quantity'],
                        'unit_price'       => $itemData['unit_price'],
                        'cost_price'       => $costPrice,
                        'discount'         => $itemData['discount'] ?? 0,
                    ]);

                    // ── SALE PRICE SYNCHRONIZATION ──────────────────────────────────────
                    // Normalize the entered package sale price to base, then recalculate
                    // all active ProductUnit sale prices from that base.
                    // Purchase / cost prices are NEVER touched here.
                    $conversionFactor = $conversionData['conversion_factor'] > 0
                        ? $conversionData['conversion_factor']
                        : 1.0;
                    $baseSalePrice = floatval($itemData['unit_price']) / $conversionFactor;

                    $this->conversionService->synchronizeProductUnitSalePrices(
                        $product,
                        $baseSalePrice
                    );
                    // ─────────────────────────────────────────────────────────────────────
                }
            }

            // Recalculate totals
            $this->recalculateSaleTotals($sale);

            // Calculate payment status
            $sale->update([
                'payment_status' => $sale->calculatePaymentStatus(),
                'due_amount' => $sale->total_amount - $sale->paid_amount,
                'udhar_amount' => max(0, $sale->total_amount - $sale->paid_amount),
            ]);

            return $sale->fresh();
        });
    }

    /**
     * Add item to sale (draft only)
     * 
     * Multi-unit support:
     * - Accepts optional unitId parameter
     * - Checks stock availability using base quantities
     * - Stores unit conversion snapshot on the item
     * - Calculates cost_price and base_cost_price for profit tracking
     *
     * @param Sale $sale
     * @param int $productId
     * @param float $quantity Quantity in selected unit
     * @param float $unitPrice Price per selected unit
     * @param float $discount Discount amount
     * @param int|null $unitId Selected unit (null = use base unit)
     * @return SaleItem
     * @throws \Exception
     */
    public function addItem(
        Sale $sale, 
        int $productId, 
        float $quantity, 
        float $unitPrice, 
        float $discount = 0,
        ?int $unitId = null
    ): SaleItem {
        if (!$sale->isDraft()) {
            throw new \Exception('Can only add items to draft sales.');
        }

        // Validate inputs
        if ($quantity <= 0) {
            throw new \Exception('Quantity must be greater than 0.');
        }
        if ($unitPrice < 0) {
            throw new \Exception('Unit price cannot be negative.');
        }

        // Calculate unit conversion data
        $conversionData = $this->conversionService->calculateTransactionData($productId, $quantity, $unitId);
        $baseQuantity = $conversionData['base_quantity'];

        // Check stock availability using BASE QUANTITIES
        $availableStock = $this->stockService->getCurrentStock($sale->warehouse_id, $productId);
        if ($availableStock < $baseQuantity) {
            throw new \Exception("Insufficient stock. Available: {$availableStock} base units, Required: {$baseQuantity} base units.");
        }

        return DB::transaction(function () use ($sale, $productId, $quantity, $unitPrice, $discount, $conversionData, $baseQuantity) {
            // Check if product is already in this sale
            $existingItem = $sale->items()->where('product_id', $productId)->first();
            
            if ($existingItem) {
                // Update existing item instead of creating duplicate
                $newQuantity = $existingItem->quantity + $quantity;
                $newBaseQuantity = $existingItem->base_quantity + $baseQuantity;
                
                // Verify increased quantity is still available
                $availableStock = $this->stockService->getCurrentStock($sale->warehouse_id, $productId);
                if ($availableStock < $newBaseQuantity) {
                    throw new \Exception("Cannot increase quantity. Available: {$availableStock} base units, Required: {$newBaseQuantity} base units.");
                }
                
                $existingItem->update([
                    'quantity' => $newQuantity,
                    'unit_price' => $unitPrice,
                    'discount' => ($existingItem->discount + $discount),
                    'base_quantity' => $newBaseQuantity,
                ]);
                
                $this->recalculateSaleTotals($sale);
                return $existingItem;
            }

            // Get product's current purchase price as cost basis
            $product = Product::find($productId);
            $baseCostPrice = $product ? $product->purchase_price : 0;
            
            // Calculate cost_price for this transaction unit
            // If selling in packages, cost_price = base cost × conversion factor
            $costPrice = $baseCostPrice;
            if ($conversionData['conversion_factor'] > 1) {
                $costPrice = $baseCostPrice * $conversionData['conversion_factor'];
            }

            $item = $sale->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'cost_price' => $costPrice,
                'discount' => $discount,
                'unit_id' => $conversionData['unit_id'],
                'conversion_factor' => $conversionData['conversion_factor'],
                'base_quantity' => $baseQuantity,
                'base_cost_price' => $baseCostPrice,
            ]);

            // Recalculate sale totals
            $this->recalculateSaleTotals($sale);

            return $item;
        });
    }

    /**
     * Update sale item (draft only)
     *
     * Multi-unit support:
     * - Can change unit along with quantity
     * - Recalculates conversion and validates stock using base quantities
     *
     * @param SaleItem $item
     * @param float $quantity Quantity in selected unit
     * @param float $unitPrice Price per selected unit
     * @param float $discount Discount amount
     * @param int|null $unitId Selected unit (null = keep existing)
     * @return SaleItem
     * @throws \Exception
     */
    public function updateItem(
        SaleItem $item, 
        float $quantity, 
        float $unitPrice, 
        float $discount = 0,
        ?int $unitId = null
    ): SaleItem {
        $sale = $item->sale;

        if (!$sale->isDraft()) {
            throw new \Exception('Can only modify items in draft sales.');
        }

        // If unitId not provided, keep the existing unit
        if ($unitId === null) {
            $unitId = $item->unit_id;
        }

        // Calculate new conversion data
        $conversionData = $this->conversionService->calculateTransactionData(
            $item->product_id, 
            $quantity, 
            $unitId
        );
        $newBaseQuantity = $conversionData['base_quantity'];

        // Check stock availability (account for current quantity being freed up)
        $currentBaseQuantity = $item->base_quantity ?? $item->quantity;
        $stockDifference = $newBaseQuantity - $currentBaseQuantity;

        if ($stockDifference > 0) {
            $availableStock = $this->stockService->getCurrentStock($sale->warehouse_id, $item->product_id);
            if ($availableStock < $stockDifference) {
                throw new \Exception("Only {$availableStock} additional base units available. Cannot increase by {$stockDifference} base units.");
            }
        }

        return DB::transaction(function () use ($item, $quantity, $unitPrice, $discount, $conversionData, $newBaseQuantity) {
            // Recalculate cost prices
            $product = Product::find($item->product_id);
            $baseCostPrice = $product ? $product->purchase_price : 0;
            
            $costPrice = $baseCostPrice;
            if ($conversionData['conversion_factor'] > 1) {
                $costPrice = $baseCostPrice * $conversionData['conversion_factor'];
            }

            $item->update([
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount' => $discount,
                'cost_price' => $costPrice,
                'unit_id' => $conversionData['unit_id'],
                'conversion_factor' => $conversionData['conversion_factor'],
                'base_quantity' => $newBaseQuantity,
                'base_cost_price' => $baseCostPrice,
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

            // Verify stock availability for all items using BASE QUANTITIES
            foreach ($sale->items as $item) {
                $baseQuantity = $item->base_quantity ?? $item->quantity;
                $currentStock = $this->stockService->getCurrentStock($sale->warehouse_id, $item->product_id);

                if ($currentStock < $baseQuantity) {
                    throw new \Exception(
                        "Insufficient stock for {$item->product->name}. " .
                        "Required: {$baseQuantity} base units, Available: {$currentStock} base units"
                    );
                }
            }

            // Create stock movements for each item using BASE QUANTITIES
            foreach ($sale->items as $item) {
                $baseQuantity = $item->base_quantity ?? $item->quantity;
                $baseCostPrice = $item->base_cost_price ?? $item->cost_price ?? $item->unit_price;
                
                $this->stockService->removeStock(
                    warehouseId: $sale->warehouse_id,
                    productId: $item->product_id,
                    quantity: $baseQuantity,
                    type: \App\Models\StockMovement::TYPE_SALE,
                    referenceType: Sale::class,
                    referenceId: $sale->id,
                    unitCost: $baseCostPrice,
                    remarks: "Sale #{$sale->invoice_number}",
                    userId: auth()->id()
                );
            }

            // Calculate payment status based on paid amount
            $dueAmount = $sale->total_amount - $paidAmount;
            
            if ($paidAmount >= $sale->total_amount) {
                $paymentStatus = Sale::PAYMENT_STATUS_PAID;
            } elseif ($paidAmount == 0) {
                $paymentStatus = Sale::PAYMENT_STATUS_UNPAID;
            } else {
                $paymentStatus = Sale::PAYMENT_STATUS_PARTIAL;
            }

            $udharAmount = max(0, $dueAmount);

            // Update sale status to CONFIRMED
            $sale->update([
                'status' => Sale::STATUS_CONFIRMED,
                'confirmed_at' => now(),
                'confirmed_by' => auth()->id(),
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'udhar_amount' => $udharAmount,
                'payment_status' => $paymentStatus,
            ]);

            // If customer exists, create ledger entry for the sale
            if ($sale->customer_id) {
                $paymentService = app(PaymentService::class);
                $paymentService->createSaleLedgerEntry($sale, auth()->id());
                
                // Record in UdharHistory
                $udharHistoryService = app(UdharHistoryService::class);
                $udharHistoryService->recordSaleCreated($sale, auth()->id());
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
            // If confirmed, create reverse stock movements using BASE QUANTITIES
            if ($sale->isConfirmed()) {
                foreach ($sale->items as $item) {
                    $baseQuantity = $item->base_quantity ?? $item->quantity;
                    $baseCostPrice = $item->base_cost_price ?? $item->cost_price ?? $item->unit_price;
                    
                    // Create reverse movement
                    $this->stockService->addStock(
                        warehouseId: $sale->warehouse_id,
                        productId: $item->product_id,
                        quantity: $baseQuantity,
                        type: StockMovement::TYPE_CUSTOMER_RETURN, // Customer return (reverse of sale)
                        unitCost: $baseCostPrice,
                        remarks: "Sale #{$sale->invoice_number} cancelled. {$reason}",
                        referenceType: 'sale_reversal',
                        referenceId: $sale->id,
                        userId: auth()->id()
                    );
                }
            }

            // Update sale status
            $sale->update([
                'status' => Sale::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            // Record in UdharHistory if customer exists
            if ($sale->customer_id) {
                $udharHistoryService = app(UdharHistoryService::class);
                $udharHistoryService->recordSaleCancelled($sale, $reason, auth()->id());
            }

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

            // Calculate new payment status
            $newDueAmount = $sale->total_amount - $newPaidAmount;
            if ($newPaidAmount == 0) {
                $paymentStatus = Sale::PAYMENT_STATUS_UNPAID;
            } elseif ($newPaidAmount >= $sale->total_amount) {
                $paymentStatus = Sale::PAYMENT_STATUS_PAID;
            } else {
                $paymentStatus = Sale::PAYMENT_STATUS_PARTIAL;
            }

            $sale->update([
                'paid_amount' => $newPaidAmount,
                'due_amount' => $newDueAmount,
                'payment_status' => $paymentStatus,
            ]);

            // Refresh the model to ensure fresh data from database
            $sale->refresh();

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
     * Check stock availability for product in warehouse
     *
     * @param int $warehouseId
     * @param int $productId
     * @param float $requestedQuantity
     * @return array
     */
    public function checkStockAvailability(int $warehouseId, int $productId, float $requestedQuantity): array
    {
        $availableStock = $this->stockService->getCurrentStock($warehouseId, $productId);
        $isAvailable = $availableStock >= $requestedQuantity;

        return [
            'available' => $isAvailable,
            'available_stock' => $availableStock,
            'requested_quantity' => $requestedQuantity,
            'remaining_after_sale' => $isAvailable ? ($availableStock - $requestedQuantity) : null,
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

        DB::table('invoice_sequences')->insertOrIgnore([
            'year' => $year,
            'next_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sequence = DB::table('invoice_sequences')
            ->where('year', $year)
            ->lockForUpdate()
            ->first();

        $latestInvoiceNumber = DB::table('sales')
            ->where('invoice_number', 'like', "INV-{$year}-%")
            ->lockForUpdate()
            ->orderByDesc('invoice_number')
            ->value('invoice_number');
        $latestNumber = $latestInvoiceNumber
            ? (int) substr($latestInvoiceNumber, strrpos($latestInvoiceNumber, '-') + 1)
            : 0;
        $nextNumber = max($sequence->next_number, $latestNumber + 1);
        
        // Ensure we never exceed 99999
        if ($nextNumber > 99999) {
            throw new \Exception("Invoice number limit exceeded for year {$year}");
        }
        
        // Increment the sequence for next time
        DB::table('invoice_sequences')
            ->where('year', $year)
            ->update([
                'next_number' => $nextNumber + 1,
                'updated_at' => now(),
            ]);
        
        return sprintf('INV-%d-%05d', $year, $nextNumber);
    }
}
