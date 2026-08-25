<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\UserNotificationPreference;
use App\Events\NotificationCreated;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

/**
 * NotificationService
 *
 * Centralized service for creating and managing notifications.
 * Handles database storage, real-time broadcasting, and preference validation.
 */
class NotificationService
{
    /**
     * Create and send a notification
     */
    public function notify(
        User|array $users,
        string $type,
        array $data,
        ?Warehouse $warehouse = null,
        string $relatedType = null,
        int|string $relatedId = null,
    ): Collection {
        // Normalize users to array
        $users = is_array($users) ? $users : [$users];

        $notifications = collect();

        foreach ($users as $user) {
            // Get user preferences
            $preferences = $this->getUserPreferences($user);

            // Check if user should receive this type
            $notificationType = $this->getNotificationCategory($type);
            if (!$preferences->shouldNotify($notificationType)) {
                continue;
            }

            // Create the notification
            $notification = Notification::create([
                'user_id' => $user->id,
                'type' => $type,
                'warehouse_id' => $warehouse?->id,
                'related_type' => $relatedType,
                'related_id' => $relatedId,
                'data' => $data,
                'status' => 'unread',
            ]);

            // Create channel records based on preferences
            $this->createChannels($notification, $preferences);

            // Broadcast real-time event
            event(new NotificationCreated($notification));

            $notifications->push($notification);
        }

        return $notifications;
    }

    /**
     * Create a notification for Super Admin when Regular Admin creates Stock Request
     */
    public function notifyStockRequestCreated(User $regularAdmin, $stockRequest, Warehouse $warehouse): Notification
    {
        $superAdmin = User::where('role', 'super_admin')->first();

        return $this->notify(
            users: $superAdmin,
            type: 'stock_request_created',
            data: [
                'title' => 'New Stock Request',
                'body' => "{$regularAdmin->name} ({$warehouse->name}) requested {$stockRequest->items->sum('requested_quantity')} bags",
                'warehouse_name' => $warehouse->name,
                'admin_name' => $regularAdmin->name,
                'request_number' => $stockRequest->request_number,
            ],
            warehouse: $warehouse,
            relatedType: 'stock_request',
            relatedId: $stockRequest->id,
        )->first();
    }

    /**
     * Create a notification for Regular Admin when their Stock Request is approved
     */
    public function notifyStockRequestApproved(User $regularAdmin, $stockRequest, Warehouse $warehouse): Notification
    {
        return $this->notify(
            users: $regularAdmin,
            type: 'stock_request_approved',
            data: [
                'title' => 'Stock Request Approved',
                'body' => "Your stock request {$stockRequest->request_number} has been approved",
                'request_number' => $stockRequest->request_number,
            ],
            warehouse: $warehouse,
            relatedType: 'stock_request',
            relatedId: $stockRequest->id,
        )->first();
    }

    /**
     * Create a notification for Regular Admin when their Stock Request is partially approved
     */
    public function notifyStockRequestPartiallyApproved(User $regularAdmin, $stockRequest, Warehouse $warehouse): Notification
    {
        return $this->notify(
            users: $regularAdmin,
            type: 'stock_request_partially_approved',
            data: [
                'title' => 'Stock Request Partially Approved',
                'body' => "Your stock request {$stockRequest->request_number} has been partially approved",
                'request_number' => $stockRequest->request_number,
            ],
            warehouse: $warehouse,
            relatedType: 'stock_request',
            relatedId: $stockRequest->id,
        )->first();
    }

    /**
     * Create a notification for Regular Admin when their Stock Request is rejected
     */
    public function notifyStockRequestRejected(User $regularAdmin, $stockRequest, Warehouse $warehouse): Notification
    {
        return $this->notify(
            users: $regularAdmin,
            type: 'stock_request_rejected',
            data: [
                'title' => 'Stock Request Rejected',
                'body' => "Your stock request {$stockRequest->request_number} has been rejected",
                'request_number' => $stockRequest->request_number,
            ],
            warehouse: $warehouse,
            relatedType: 'stock_request',
            relatedId: $stockRequest->id,
        )->first();
    }

    /**
     * Create a notification when Stock Request is cancelled
     */
    public function notifyStockRequestCancelled(User $requestedBy, $stockRequest, Warehouse $warehouse): Notification
    {
        $superAdmin = User::where('role', 'super_admin')->first();

        return $this->notify(
            users: $superAdmin,
            type: 'stock_request_cancelled',
            data: [
                'title' => 'Stock Request Cancelled',
                'body' => "{$requestedBy->name} cancelled stock request {$stockRequest->request_number}",
                'request_number' => $stockRequest->request_number,
                'admin_name' => $requestedBy->name,
            ],
            warehouse: $warehouse,
            relatedType: 'stock_request',
            relatedId: $stockRequest->id,
        )->first();
    }

    /**
     * Create a notification when Stock Transfer is created
     */
    public function notifyStockTransferCreated(User $regularAdmin, $stockTransfer, Warehouse $warehouse): Notification
    {
        return $this->notify(
            users: $regularAdmin,
            type: 'stock_transfer_created',
            data: [
                'title' => 'Stock Transfer Created',
                'body' => "A stock transfer to {$warehouse->name} has been created",
                'transfer_number' => $stockTransfer->transfer_number ?? 'N/A',
                'warehouse_name' => $warehouse->name,
            ],
            warehouse: $warehouse,
            relatedType: 'stock_transfer',
            relatedId: $stockTransfer->id,
        )->first();
    }

    /**
     * Create a notification when Stock Transfer is dispatched
     */
    public function notifyStockTransferDispatched(User $regularAdmin, $stockTransfer, Warehouse $warehouse): Notification
    {
        return $this->notify(
            users: $regularAdmin,
            type: 'stock_transfer_dispatched',
            data: [
                'title' => 'Stock Transfer Dispatched',
                'body' => "Stock transfer to {$warehouse->name} has been dispatched",
                'warehouse_name' => $warehouse->name,
            ],
            warehouse: $warehouse,
            relatedType: 'stock_transfer',
            relatedId: $stockTransfer->id,
        )->first();
    }

    /**
     * Create a notification when Stock Transfer is received
     */
    public function notifyStockTransferReceived(User $regularAdmin, $stockTransfer, Warehouse $warehouse): Notification
    {
        return $this->notify(
            users: $regularAdmin,
            type: 'stock_transfer_received',
            data: [
                'title' => 'Stock Transfer Received',
                'body' => "Stock transfer to {$warehouse->name} has been received",
                'warehouse_name' => $warehouse->name,
            ],
            warehouse: $warehouse,
            relatedType: 'stock_transfer',
            relatedId: $stockTransfer->id,
        )->first();
    }

    /**
     * Create a notification when someone sends a chat message
     */
    public function notifyChatMessage(User $sender, User $recipient, $conversation, $message, Warehouse $warehouse): Notification
    {
        return $this->notify(
            users: $recipient,
            type: 'chat_message',
            data: [
                'title' => 'New Message',
                'body' => "{$sender->name}: " . substr($message->message, 0, 100),
                'sender_name' => $sender->name,
                'conversation_id' => $conversation->id,
            ],
            warehouse: $warehouse,
            relatedType: 'conversation',
            relatedId: $conversation->id,
        )->first();
    }

    /**
     * Get user preferences or create default
     */
    public function getUserPreferences(User $user): UserNotificationPreference
    {
        return UserNotificationPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'notify_stock_requests' => true,
                'notify_stock_transfers' => true,
                'notify_messages' => true,
                'notify_warehouse_updates' => true,
                'send_email' => true,
                'send_in_app' => true,
                'enable_browser_notifications' => true,
            ]
        );
    }

    /**
     * Get total unread count for user
     */
    public function getUnreadCount(User $user): int
    {
        return Notification::forUser($user)
            ->forUserWarehouses($user)
            ->unread()
            ->count();
    }

    /**
     * Get recent unread notifications for user
     */
    public function getRecentUnread(User $user, int $limit = 10): Collection
    {
        return Notification::forUser($user)
            ->forUserWarehouses($user)
            ->unread()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get paginated notifications for user
     */
    public function getPaginated(User $user, int $perPage = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Notification::forUser($user)
            ->forUserWarehouses($user)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Mark all as read for user
     */
    public function markAllAsRead(User $user): int
    {
        return Notification::forUser($user)
            ->forUserWarehouses($user)
            ->unread()
            ->update([
                'status' => 'read',
                'read_at' => now(),
            ]);
    }

    /**
     * Delete old notifications (older than X days)
     */
    public function cleanup(int $days = 90): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))
            ->delete();
    }

    /**
     * Get notification category from type
     */
    private function getNotificationCategory(string $type): string
    {
        return match($type) {
            'stock_request_created',
            'stock_request_approved',
            'stock_request_partially_approved',
            'stock_request_rejected',
            'stock_request_cancelled' => 'stock_request',
            
            'stock_transfer_created',
            'stock_transfer_dispatched',
            'stock_transfer_received' => 'stock_transfer',
            
            'chat_message' => 'message',
            
            default => 'warehouse_update',
        };
    }

    /**
     * Create channel records for notification
     */
    private function createChannels(Notification $notification, UserNotificationPreference $preferences): void
    {
        // Create email channel if enabled
        if ($preferences->shouldNotifyVia('email')) {
            $notification->channels()->create([
                'channel' => 'email',
                'status' => 'pending',
            ]);
        }

        // Create in-app channel if enabled
        if ($preferences->shouldNotifyVia('in_app')) {
            $notification->channels()->create([
                'channel' => 'in_app',
                'status' => 'sent',
            ]);
        }

        // Create browser notification channel if enabled
        if ($preferences->shouldNotifyVia('browser')) {
            $notification->channels()->create([
                'channel' => 'browser',
                'status' => 'pending',
            ]);
        }
    }
}
