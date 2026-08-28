<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Models\User;

/**
 * ConversationInitializationService
 * 
 * Handles initialization of warehouse conversations.
 * This service ensures that every warehouse has a conversation
 * with participants when needed.
 */
class ConversationInitializationService
{
    public function __construct(protected ChatService $chatService)
    {
    }

    /**
     * Initialize conversations for all active warehouses
     */
    public function initializeAllWarehouseConversations(): int
    {
        $warehouses = Warehouse::where('status', 'active')->get();
        $count = 0;

        \Log::info('ConversationInitialization: Found ' . $warehouses->count() . ' active warehouses');

        foreach ($warehouses as $warehouse) {
            \Log::info('ConversationInitialization: Processing warehouse ' . $warehouse->id . ' (' . $warehouse->name . ')');
            if ($this->initializeWarehouseConversation($warehouse)) {
                $count++;
                \Log::info('ConversationInitialization: Successfully initialized warehouse ' . $warehouse->id);
            } else {
                \Log::warning('ConversationInitialization: Failed to initialize warehouse ' . $warehouse->id);
            }
        }

        \Log::info('ConversationInitialization: Initialized ' . $count . ' warehouse conversations');

        return $count;
    }

    /**
     * Initialize conversation for a specific warehouse
     */
    public function initializeWarehouseConversation(Warehouse $warehouse): bool
    {
        try {
            $this->chatService->ensureWarehouseParticipants($warehouse);
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to initialize conversation for warehouse {$warehouse->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update conversation participants when admin assigned
     */
    public function handleAdminAssignedToWarehouse(Warehouse $warehouse, User $admin): void
    {
        if ($admin->isSuperAdmin()) {
            return; // Super admin doesn't need warehouse assignment
        }

        $this->chatService->updateWarehouseAdmin($warehouse, $admin);
    }

    /**
     * Handle warehouse status change
     */
    public function handleWarehouseStatusChanged(Warehouse $warehouse, string $oldStatus, string $newStatus): void
    {
        if ($oldStatus === 'active' && $newStatus !== 'active') {
            // Warehouse deactivated - can archive conversations or just stop showing them
            // We don't delete conversations to preserve history
        } elseif ($oldStatus !== 'active' && $newStatus === 'active') {
            // Warehouse activated - ensure conversation exists
            $this->initializeWarehouseConversation($warehouse);
        }
    }

    /**
     * Handle admin removal or deactivation
     */
    public function handleAdminRemovedFromWarehouse(Warehouse $warehouse, User $admin): void
    {
        if ($admin->isSuperAdmin()) {
            return;
        }

        $this->chatService->updateWarehouseAdmin($warehouse, null);
    }
}
