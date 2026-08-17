<?php

namespace App\Services;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\DB;

class StockTransferService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Create a new draft stock transfer
     *
     * @param array $data
     * @return StockTransfer
     * @throws \Exception
     */
    public function createTransfer(array $data): StockTransfer
    {
        // Validate warehouses are different
        if ($data['source_warehouse_id'] === $data['destination_warehouse_id']) {
            throw new \Exception('Source and destination warehouses must be different.');
        }

        return DB::transaction(function () use ($data) {
            $transfer = StockTransfer::create([
                'transfer_number' => $this->generateTransferNumber(),
                'source_warehouse_id' => $data['source_warehouse_id'],
                'destination_warehouse_id' => $data['destination_warehouse_id'],
                'transfer_date' => $data['transfer_date'],
                'status' => StockTransfer::STATUS_DRAFT,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            return $transfer;
        });
    }

    /**
     * Add item to transfer (draft only)
     *
     * @param StockTransfer $transfer
     * @param int $productId
     * @param float $quantity
     * @return StockTransferItem
     * @throws \Exception
     */
    public function addItem(StockTransfer $transfer, int $productId, float $quantity): StockTransferItem
    {
        if (!$transfer->isDraft()) {
            throw new \Exception('Can only add items to draft transfers.');
        }

        if ($quantity <= 0) {
            throw new \Exception('Quantity must be greater than zero.');
        }

        // Check stock availability in source warehouse
        $availableStock = $this->stockService->getCurrentStock($transfer->source_warehouse_id, $productId);
        if ($availableStock < $quantity) {
            throw new \Exception(
                "Insufficient stock in source warehouse. Available: {$availableStock}, Requested: {$quantity}"
            );
        }

        return DB::transaction(function () use ($transfer, $productId, $quantity) {
            return $transfer->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'received_quantity' => 0,
            ]);
        });
    }

    /**
     * Update item in transfer (draft only)
     *
     * @param StockTransferItem $item
     * @param float $quantity
     * @return StockTransferItem
     * @throws \Exception
     */
    public function updateItem(StockTransferItem $item, float $quantity): StockTransferItem
    {
        $transfer = $item->transfer;

        if (!$transfer->isDraft()) {
            throw new \Exception('Can only update items in draft transfers.');
        }

        if ($quantity <= 0) {
            throw new \Exception('Quantity must be greater than zero.');
        }

        // Check stock availability (accounting for current quantity)
        $currentQuantity = $item->quantity;
        $quantityDifference = $quantity - $currentQuantity;

        if ($quantityDifference > 0) {
            $availableStock = $this->stockService->getCurrentStock($transfer->source_warehouse_id, $item->product_id);
            if ($availableStock < $quantityDifference) {
                throw new \Exception(
                    "Insufficient stock for additional quantity. Available: {$availableStock}, " .
                    "Additional needed: {$quantityDifference}"
                );
            }
        }

        return DB::transaction(function () use ($item, $quantity) {
            $item->update(['quantity' => $quantity]);
            return $item;
        });
    }

    /**
     * Remove item from transfer (draft only)
     *
     * @param StockTransferItem $item
     * @return void
     * @throws \Exception
     */
    public function removeItem(StockTransferItem $item): void
    {
        if (!$item->transfer->isDraft()) {
            throw new \Exception('Can only remove items from draft transfers.');
        }

        DB::transaction(function () use ($item) {
            $item->delete();
        });
    }

    /**
     * Submit transfer for approval
     *
     * @param StockTransfer $transfer
     * @return StockTransfer
     * @throws \Exception
     */
    public function submitForApproval(StockTransfer $transfer): StockTransfer
    {
        if (!$transfer->canBeSubmitted()) {
            throw new \Exception('Transfer must be draft with at least one item.');
        }

        return DB::transaction(function () use ($transfer) {
            $transfer->update([
                'status' => StockTransfer::STATUS_PENDING_APPROVAL,
            ]);

            return $transfer;
        });
    }

    /**
     * Approve transfer
     *
     * @param StockTransfer $transfer
     * @return StockTransfer
     * @throws \Exception
     */
    public function approveTransfer(StockTransfer $transfer): StockTransfer
    {
        if (!$transfer->canBeApproved()) {
            throw new \Exception('Only pending approval transfers can be approved.');
        }

        return DB::transaction(function () use ($transfer) {
            $transfer->update([
                'status' => StockTransfer::STATUS_APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            return $transfer;
        });
    }

    /**
     * Dispatch transfer (creates transfer_out stock movement)
     * ATOMIC with row-level locking to prevent stock corruption
     *
     * @param StockTransfer $transfer
     * @return StockTransfer
     * @throws \Exception
     */
    public function dispatchTransfer(StockTransfer $transfer): StockTransfer
    {
        if (!$transfer->canBeDispatched()) {
            throw new \Exception('Only approved transfers can be dispatched.');
        }

        if ($transfer->items()->count() === 0) {
            throw new \Exception('Transfer must have items to dispatch.');
        }

        return DB::transaction(function () use ($transfer) {
            // Lock source warehouse inventory rows
            $sourceInventoryRows = \App\Models\WarehouseInventory::where('warehouse_id', $transfer->source_warehouse_id)
                ->lockForUpdate()
                ->get();

            // Verify stock availability for all items
            foreach ($transfer->items as $item) {
                $currentStock = $this->stockService->getCurrentStock($transfer->source_warehouse_id, $item->product_id);

                if ($currentStock < $item->quantity) {
                    throw new \Exception(
                        "Insufficient stock for {$item->product->name}. " .
                        "Required: {$item->quantity}, Available: {$currentStock}"
                    );
                }
            }

            // Create transfer_out stock movements for each item
            foreach ($transfer->items as $item) {
                $this->stockService->removeStock(
                    warehouseId: $transfer->source_warehouse_id,
                    productId: $item->product_id,
                    quantity: $item->quantity,
                    unitCost: 0, // Transfer has no cost impact
                    reason: "Transfer #{$transfer->transfer_number} dispatched",
                    referenceType: 'transfer_out',
                    referenceId: $transfer->id,
                    createdBy: auth()->id()
                );
            }

            // Update transfer status
            $transfer->update([
                'status' => StockTransfer::STATUS_DISPATCHED,
                'dispatched_by' => auth()->id(),
                'dispatched_at' => now(),
            ]);

            return $transfer;
        });
    }

    /**
     * Mark transfer as in transit
     *
     * @param StockTransfer $transfer
     * @return StockTransfer
     * @throws \Exception
     */
    public function markInTransit(StockTransfer $transfer): StockTransfer
    {
        if (!$transfer->canBeInTransit()) {
            throw new \Exception('Only dispatched transfers can be marked as in transit.');
        }

        return DB::transaction(function () use ($transfer) {
            $transfer->update([
                'status' => StockTransfer::STATUS_IN_TRANSIT,
                'in_transit_at' => now(),
            ]);

            return $transfer;
        });
    }

    /**
     * Receive transfer (creates transfer_in stock movement)
     * ATOMIC to prevent duplicate receipts
     *
     * @param StockTransfer $transfer
     * @param array $receivedItems  Format: [item_id => quantity]
     * @return StockTransfer
     * @throws \Exception
     */
    public function receiveTransfer(StockTransfer $transfer, array $receivedItems): StockTransfer
    {
        if (!$transfer->canBeReceived()) {
            throw new \Exception('Only in-transit transfers can be received.');
        }

        // Validate received items
        foreach ($receivedItems as $itemId => $quantity) {
            $item = StockTransferItem::find($itemId);
            if (!$item || $item->stock_transfer_id !== $transfer->id) {
                throw new \Exception("Invalid item: {$itemId}");
            }
            if ($quantity <= 0 || $quantity > $item->quantity) {
                throw new \Exception(
                    "Invalid quantity for item {$item->id}. Max: {$item->quantity}"
                );
            }
            if ($quantity + $item->received_quantity > $item->quantity) {
                throw new \Exception(
                    "Received quantity exceeds transfer quantity for item {$item->id}"
                );
            }
        }

        return DB::transaction(function () use ($transfer, $receivedItems) {
            // Lock destination warehouse inventory rows
            $destInventoryRows = \App\Models\WarehouseInventory::where('warehouse_id', $transfer->destination_warehouse_id)
                ->lockForUpdate()
                ->get();

            // Process each received item
            foreach ($receivedItems as $itemId => $quantity) {
                $item = StockTransferItem::find($itemId);

                // Create transfer_in stock movement
                $this->stockService->addStock(
                    warehouseId: $transfer->destination_warehouse_id,
                    productId: $item->product_id,
                    quantity: $quantity,
                    unitCost: 0, // Transfer has no cost impact
                    reason: "Transfer #{$transfer->transfer_number} received",
                    referenceType: 'transfer_in',
                    referenceId: $transfer->id,
                    createdBy: auth()->id()
                );

                // Update received quantity
                $item->update([
                    'received_quantity' => $item->received_quantity + $quantity,
                ]);
            }

            // Check if all items are fully received
            $allReceived = true;
            foreach ($transfer->items as $item) {
                if ($item->received_quantity < $item->quantity) {
                    $allReceived = false;
                    break;
                }
            }

            // Update transfer status
            $status = $allReceived ? StockTransfer::STATUS_RECEIVED : StockTransfer::STATUS_IN_TRANSIT;
            $transfer->update([
                'status' => $status,
                'received_by' => auth()->id(),
                'received_at' => $allReceived ? now() : $transfer->received_at,
            ]);

            return $transfer;
        });
    }

    /**
     * Cancel transfer (no stock reversal for dispatched)
     * Draft/pending/approved transfers: no stock effect
     * Dispatched/in-transit transfers: manual reversal needed via StockService
     *
     * @param StockTransfer $transfer
     * @param string $reason
     * @return StockTransfer
     * @throws \Exception
     */
    public function cancelTransfer(StockTransfer $transfer, string $reason = ''): StockTransfer
    {
        if (!$transfer->canBeCancelled()) {
            throw new \Exception('Cannot cancel a received or already cancelled transfer.');
        }

        return DB::transaction(function () use ($transfer, $reason) {
            // No automatic stock reversal for dispatched transfers
            // That must be done manually via adjustment if needed

            $transfer->update([
                'status' => StockTransfer::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'notes' => ($transfer->notes ? $transfer->notes . "\n" : '') . 
                          "Cancelled by " . auth()->user()->name . ": {$reason}",
            ]);

            return $transfer;
        });
    }

    /**
     * Generate unique transfer number
     *
     * @return string
     */
    protected function generateTransferNumber(): string
    {
        $year = now()->year;
        $latestTransfer = StockTransfer::whereYear('created_at', $year)
            ->latest('id')
            ->first();

        $nextNumber = ($latestTransfer ? (int)substr($latestTransfer->transfer_number, -5) + 1 : 1);

        return sprintf('TRF-%d-%05d', $year, $nextNumber);
    }

    /**
     * Get transfer summary
     *
     * @param StockTransfer $transfer
     * @return array
     */
    public function getTransferSummary(StockTransfer $transfer): array
    {
        $items = $transfer->items;
        $totalQuantity = (float)$items->sum('quantity');
        $totalReceived = (float)$items->sum('received_quantity');
        $pendingQuantity = $totalQuantity - $totalReceived;

        return [
            'items_count' => $items->count(),
            'total_quantity' => $totalQuantity,
            'total_received' => $totalReceived,
            'pending_quantity' => $pendingQuantity,
            'completion_percentage' => $totalQuantity > 0 ? ($totalReceived / $totalQuantity) * 100 : 0,
            'all_received' => $totalQuantity > 0 && $totalReceived == $totalQuantity,
        ];
    }
}
