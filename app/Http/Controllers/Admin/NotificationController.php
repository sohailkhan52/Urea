<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * NotificationController
 *
 * Handles notification display, management, and preferences.
 */
class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Display notifications page
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get unread count
        $unreadCount = $this->notificationService->getUnreadCount($user);

        // Get paginated notifications
        $notifications = $this->notificationService->getPaginated($user, 20);

        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Get unread count (API)
     */
    public function getUnreadCount(Request $request)
    {
        $user = auth()->user();
        $count = $this->notificationService->getUnreadCount($user);

        return response()->json([
            'success' => true,
            'unread_count' => $count,
        ]);
    }

    /**
     * Get recent unread notifications (API)
     */
    public function getRecent(Request $request)
    {
        $user = auth()->user();
        $limit = $request->query('limit', 10);

        $notifications = $this->notificationService->getRecentUnread($user, $limit)
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'type_label' => $notification->getTypeLabel(),
                    'title' => $notification->getTitle(),
                    'body' => $notification->getBody(),
                    'icon' => $notification->getIcon(),
                    'route' => $notification->getRoute(),
                    'created_at' => $notification->created_at->toIso8601String(),
                    'created_at_formatted' => $notification->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => count($notifications),
        ]);
    }

    /**
     * Mark notification as read (API)
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        $user = auth()->user();

        // Verify user owns this notification
        if ($notification->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verify warehouse access (if applicable)
        if ($notification->warehouse_id && !$user->isSuperAdmin()) {
            if ($user->warehouse_id !== $notification->warehouse_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => $this->notificationService->getUnreadCount($user),
        ]);
    }

    /**
     * Mark all notifications as read (API)
     */
    public function markAllAsRead(Request $request)
    {
        $user = auth()->user();

        $count = $this->notificationService->markAllAsRead($user);

        return response()->json([
            'success' => true,
            'marked_count' => $count,
            'unread_count' => 0,
        ]);
    }

    /**
     * Delete a notification (API)
     */
    public function delete(Request $request, Notification $notification)
    {
        $user = auth()->user();

        // Verify user owns this notification
        if ($notification->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->update(['status' => 'deleted']);

        return response()->json([
            'success' => true,
            'unread_count' => $this->notificationService->getUnreadCount($user),
        ]);
    }

    /**
     * Get notification preferences
     */
    public function getPreferences(Request $request)
    {
        $user = auth()->user();
        $preferences = $this->notificationService->getUserPreferences($user);

        return response()->json([
            'success' => true,
            'preferences' => [
                'notify_stock_requests' => $preferences->notify_stock_requests,
                'notify_stock_transfers' => $preferences->notify_stock_transfers,
                'notify_messages' => $preferences->notify_messages,
                'notify_warehouse_updates' => $preferences->notify_warehouse_updates,
                'send_email' => $preferences->send_email,
                'send_in_app' => $preferences->send_in_app,
                'enable_browser_notifications' => $preferences->enable_browser_notifications,
                'do_not_disturb' => $preferences->do_not_disturb,
                'dnd_start' => $preferences->dnd_start?->format('H:i'),
                'dnd_end' => $preferences->dnd_end?->format('H:i'),
            ],
        ]);
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'notify_stock_requests' => 'boolean',
            'notify_stock_transfers' => 'boolean',
            'notify_messages' => 'boolean',
            'notify_warehouse_updates' => 'boolean',
            'send_email' => 'boolean',
            'send_in_app' => 'boolean',
            'enable_browser_notifications' => 'boolean',
            'do_not_disturb' => 'boolean',
            'dnd_start' => 'nullable|date_format:H:i',
            'dnd_end' => 'nullable|date_format:H:i',
        ]);

        $preferences = $this->notificationService->getUserPreferences($user);
        $preferences->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Preferences updated successfully',
        ]);
    }
}
