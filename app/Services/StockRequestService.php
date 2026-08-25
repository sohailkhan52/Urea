<?php

namespace App\Services;

use App\Models\StockRequest;
use App\Models\StockRequestItem;
use Illuminate\Support\Facades\DB;

/**
 * StockRequestService
 * 
 * Handles all business logic for stock requests.
 * Ensures proper status transitions, validations, and data integrity.
 */
class StockRequestService
{
    /**
     * Create a new stock request
     * 
     * @param array $data
     * @return StockRequest
     * @throws \Exception
     */
    public function createRequest(array $data): StockRequest
    {
        // Validate user has access to warehouse
        $user = auth()->user();
        
        if ($user->isSuperAdmin()) {
            // Super admin can use any warehouse
            if (!$user->canAccessWarehouse($data['warehouse_id'])) {
                throw new \Exception('You do not have permission to create requests for this warehouse.');
            }
        } else {
            // Non-super-admin users are forced to use the default warehouse
            $defaultWarehouse = \App\Models\Warehouse::getDefault();
            
            if (!$defaultWarehouse) {
                throw new \Exception('No default warehouse found. Please contact system administrator.');
            }
            
            // Override the warehouse_id to always use the default warehouse for non-super-admin
            $data['warehouse_id'] = $defaultWarehouse->id;
        }

        return DB::transaction(function () use ($data, $user) {
            $request = StockRequest::create([
                'request_number' => $this->generateRequestNumber(),
                'warehouse_id' => $data['warehouse_id'],
                'requested_by' => $user->id,
                'status' => StockRequest::STATUS_PENDING,
                'priority' => $data['priority'] ?? StockRequest::PRIORITY_NORMAL,
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            return $request;
        });
    }

    /**
     * Add item to stock request
     * 
     * @param StockRequest $request
     * @param int $productId
     * @param float $quantity
     * @param string|null $notes
     * @return StockRequestItem
     * @throws \Exception
     */
    public function addItem(StockRequest $request, int $productId, float $quantity, ?string $notes = null): StockRequestItem
    {
        if (!$request->canBeEdited()) {
            throw new \Exception('Cannot add items to a request that is not in pending status.');
        }

        if ($quantity <= 0) {
            throw new \Exception('Quantity must be greater than zero.');
        }

        // Check if product already exists in request
        if ($request->items()->where('product_id', $productId)->exists()) {
            throw new \Exception('This product is already in the request. Please update the existing item instead.');
        }

        return DB::transaction(function () use ($request, $productId, $quantity, $notes) {
            return $request->items()->create([
                'product_id' => $productId,
                'requested_quantity' => $quantity,
                'approved_quantity' => 0,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Update item in stock request
     * 
     * @param StockRequestItem $item
     * @param float $quantity
     * @param string|null $notes
     * @return StockRequestItem
     * @throws \Exception
     */
    public function updateItem(StockRequestItem $item, float $quantity, ?string $notes = null): StockRequestItem
    {
        if (!$item->stockRequest->canBeEdited()) {
            throw new \Exception('Cannot update items in a request that is not in pending status.');
        }

        if ($quantity <= 0) {
            throw new \Exception('Quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($item, $quantity, $notes) {
            $item->update([
                'requested_quantity' => $quantity,
                'notes' => $notes,
            ]);

            return $item;
        });
    }

    /**
     * Remove item from stock request
     * 
     * @param StockRequestItem $item
     * @return void
     * @throws \Exception
     */
    public function removeItem(StockRequestItem $item): void
    {
        if (!$item->stockRequest->canBeEdited()) {
            throw new \Exception('Cannot remove items from a request that is not in pending status.');
        }

        DB::transaction(function () use ($item) {
            $item->delete();
        });
    }

    /**
     * Submit request for review (change from pending to under_review)
     * 
     * @param StockRequest $request
     * @return StockRequest
     * @throws \Exception
     */
    public function submitForReview(StockRequest $request): StockRequest
    {
        if (!$request->isPending()) {
            throw new \Exception('Only pending requests can be submitted for review.');
        }

        if ($request->items()->count() === 0) {
            throw new \Exception('Cannot submit a request with no items.');
        }

        return DB::transaction(function () use ($request) {
            $request->update([
                'status' => StockRequest::STATUS_UNDER_REVIEW,
            ]);

            return $request;
        });
    }

    /**
     * Approve stock request (fully or partially)
     * 
     * @param StockRequest $request
     * @param array $approvedQuantities [item_id => quantity]
     * @param string|null $notes
     * @return StockRequest
     * @throws \Exception
     */
    public function approveRequest(StockRequest $request, array $approvedQuantities, ?string $notes = null): StockRequest
    {
        if (!$request->canBeApproved()) {
            throw new \Exception('This request cannot be approved in its current status.');
        }

        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            throw new \Exception('Only Super Admin can approve requests.');
        }

        return DB::transaction(function () use ($request, $approvedQuantities, $notes, $user) {
            $isFullyApproved = true;
            $isPartiallyApproved = false;

            // Update approved quantities for each item
            foreach ($request->items as $item) {
                $approvedQty = $approvedQuantities[$item->id] ?? 0;

                if ($approvedQty < 0) {
                    throw new \Exception('Approved quantity cannot be negative.');
                }

                if ($approvedQty > $item->requested_quantity) {
                    throw new \Exception('Approved quantity cannot exceed requested quantity.');
                }

                $item->update(['approved_quantity' => $approvedQty]);

                // Check approval status
                if ($approvedQty < $item->requested_quantity) {
                    $isFullyApproved = false;
                }
                if ($approvedQty > 0) {
                    $isPartiallyApproved = true;
                }
            }

            // Determine final status
            if ($isFullyApproved && $isPartiallyApproved) {
                $status = StockRequest::STATUS_APPROVED;
            } elseif ($isPartiallyApproved) {
                $status = StockRequest::STATUS_PARTIALLY_APPROVED;
            } else {
                throw new \Exception('At least one item must have an approved quantity greater than zero.');
            }

            $request->update([
                'status' => $status,
                'approved_by' => $user->id,
                'approved_at' => now(),
                'approval_notes' => $notes,
            ]);

            return $request;
        });
    }

    /**
     * Reject stock request
     * 
     * @param StockRequest $request
     * @param string $reason
     * @return StockRequest
     * @throws \Exception
     */
    public function rejectRequest(StockRequest $request, string $reason): StockRequest
    {
        if (!$request->canBeRejected()) {
            throw new \Exception('This request cannot be rejected in its current status.');
        }

        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            throw new \Exception('Only Super Admin can reject requests.');
        }

        if (empty(trim($reason))) {
            throw new \Exception('A rejection reason is required.');
        }

        return DB::transaction(function () use ($request, $reason, $user) {
            $request->update([
                'status' => StockRequest::STATUS_REJECTED,
                'rejected_by' => $user->id,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);

            return $request;
        });
    }

    /**
     * Cancel stock request (by requester)
     * 
     * @param StockRequest $request
     * @param string|null $reason
     * @return StockRequest
     * @throws \Exception
     */
    public function cancelRequest(StockRequest $request, ?string $reason = null): StockRequest
    {
        if (!$request->canBeCancelled()) {
            throw new \Exception('This request cannot be cancelled in its current status.');
        }

        $user = auth()->user();

        // Regular admin can only cancel their own requests
        if (!$user->isSuperAdmin() && $request->requested_by !== $user->id) {
            throw new \Exception('You can only cancel your own requests.');
        }

        return DB::transaction(function () use ($request, $reason, $user) {
            $request->update([
                'status' => StockRequest::STATUS_CANCELLED,
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            return $request;
        });
    }

    /**
     * Link stock request to a stock transfer
     * 
     * @param StockRequest $request
     * @param int $stockTransferId
     * @return StockRequest
     * @throws \Exception
     */
    public function linkToTransfer(StockRequest $request, int $stockTransferId): StockRequest
    {
        if (!$request->canCreateTransfer()) {
            throw new \Exception('Transfer can only be created from approved or partially approved requests.');
        }

        return DB::transaction(function () use ($request, $stockTransferId) {
            $request->update([
                'status' => StockRequest::STATUS_TRANSFER_CREATED,
                'stock_transfer_id' => $stockTransferId,
            ]);

            return $request;
        });
    }

    /**
     * Generate unique request number
     * 
     * @return string
     */
    protected function generateRequestNumber(): string
    {
        $year = now()->year;
        $latestRequest = StockRequest::whereYear('created_at', $year)
            ->latest('id')
            ->first();

        $nextNumber = ($latestRequest ? (int)substr($latestRequest->request_number, -5) + 1 : 1);

        return sprintf('SR-%d-%05d', $year, $nextNumber);
    }

    /**
     * Get request summary statistics
     * 
     * @param StockRequest $request
     * @return array
     */
    public function getRequestSummary(StockRequest $request): array
    {
        $items = $request->items;
        
        $totalRequestedQty = $items->sum('requested_quantity');
        $totalApprovedQty = $items->sum('approved_quantity');
        $totalItems = $items->count();
        
        $fullyApprovedItems = $items->filter(fn($item) => $item->isFullyApproved())->count();
        $partiallyApprovedItems = $items->filter(fn($item) => $item->isPartiallyApproved())->count();
        $notApprovedItems = $items->filter(fn($item) => $item->isNotApproved())->count();

        $approvalPercentage = $totalRequestedQty > 0 
            ? ($totalApprovedQty / $totalRequestedQty) * 100 
            : 0;

        return [
            'total_items' => $totalItems,
            'total_requested_quantity' => $totalRequestedQty,
            'total_approved_quantity' => $totalApprovedQty,
            'fully_approved_items' => $fullyApprovedItems,
            'partially_approved_items' => $partiallyApprovedItems,
            'not_approved_items' => $notApprovedItems,
            'approval_percentage' => round($approvalPercentage, 2),
        ];
    }
}
