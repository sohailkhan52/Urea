<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\ChatService;
use App\Events\MessageSent;
use App\Events\MessageRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ChatController
 * 
 * Handles all chat and messaging operations with WebSocket support.
 */
class ChatController extends Controller
{
    public function __construct(protected ChatService $chatService)
    {
    }

    /**
     * Display chat page
     */
    public function index(Request $request)
    {
        // Check multi-warehouse
        ensureMultiWarehouseEnabled();

        $user = auth()->user();

        // Debug: Log active warehouses
        $activeWarehouses = \App\Models\Warehouse::where('status', 'active')->count();
        $existingConversations = \App\Models\Conversation::count();
        
        \Log::info('Chat Index - Active warehouses: ' . $activeWarehouses . ', Existing conversations: ' . $existingConversations);

        // Debug: Ensure conversations are initialized
        try {
            $initService = app(\App\Services\ConversationInitializationService::class);
            $count = $initService->initializeAllWarehouseConversations();
            \Log::info('Chat Index - Initialized ' . $count . ' warehouse conversations');
        } catch (\Exception $e) {
            \Log::warning('Chat index - Failed to initialize conversations: ' . $e->getMessage());
        }

        // Get all accessible conversations for user
        $conversations = $this->chatService->getUserConversations($user);
        
        \Log::info('Chat Index - Retrieved ' . $conversations->count() . ' conversations for user ' . $user->id);

        // Get total unread count
        $totalUnread = $this->chatService->getTotalUnreadCount($user);

        return view('admin.chat.index', compact('conversations', 'totalUnread'));
    }

    /**
     * Show conversation (for initial load, WebSocket handles real-time)
     */
    public function show(Request $request, Conversation $conversation)
    {
        // Check multi-warehouse
        ensureMultiWarehouseEnabled();

        $user = auth()->user();

        // Verify access
        if (!$conversation->canAccess($user)) {
            abort(403, 'You do not have access to this conversation.');
        }

        // Get paginated messages
        $messages = $this->chatService->getMessages($conversation, $user, perPage: 50);

        // Get conversation with status
        $conversationStatus = $this->chatService->getConversationWithStatus($conversation, $user);

        // Mark as read (latest messages)
        $latestMessage = $conversation->messages()->latest('created_at')->first();
        if ($latestMessage) {
            $this->chatService->markAsRead($conversation, $user, $latestMessage);
        }

        return view('admin.chat.show', compact('conversation', 'messages', 'conversationStatus'));
    }

    /**
     * Send message via API
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        // Check multi-warehouse
        ensureMultiWarehouseEnabled();

        $user = auth()->user();

        // Verify access
        if (!$conversation->canAccess($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validate
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'related_type' => 'nullable|string|max:50',
            'related_id' => 'nullable|integer',
        ]);

        try {
            $message = $this->chatService->sendMessage(
                sender: $user,
                conversation: $conversation,
                message: $validated['message'],
                messageType: 'text',
                relatedType: $validated['related_type'] ?? null,
                relatedId: $validated['related_id'] ?? null,
            );

            // Broadcast event
            event(new MessageSent($message));

            return response()->json([
                'success' => true,
                'message' => $message->load('sender', 'reads'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Mark message as read
     */
    public function markAsRead(Request $request, Conversation $conversation, Message $message)
    {
        // Check multi-warehouse
        ensureMultiWarehouseEnabled();

        $user = auth()->user();

        // Verify access
        if (!$conversation->canAccess($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($message->conversation_id !== $conversation->id) {
            return response()->json(['error' => 'Message does not belong to this conversation'], 400);
        }

        try {
            $message->markAsReadBy($user);

            // Broadcast event
            event(new MessageRead($conversation, $user, $message->id));

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Mark all messages as read in conversation
     */
    public function markAllAsRead(Request $request, Conversation $conversation)
    {
        // Check multi-warehouse
        ensureMultiWarehouseEnabled();

        $user = auth()->user();

        // Verify access
        if (!$conversation->canAccess($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $this->chatService->markAsRead($conversation, $user);

            return response()->json([
                'success' => true,
                'unread_count' => $conversation->unreadCount($user),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get conversations list (for sidebar/dropdown)
     */
    public function listConversations(Request $request)
    {
        // Check multi-warehouse
        ensureMultiWarehouseEnabled();

        $user = auth()->user();

        // First, ensure all conversations are initialized
        try {
            $initService = app(\App\Services\ConversationInitializationService::class);
            $initService->initializeAllWarehouseConversations();
        } catch (\Exception $e) {
            \Log::warning('ListConversations - Failed to initialize: ' . $e->getMessage());
        }

        $conversations = $this->chatService->getUserConversations($user)
            ->map(function ($conversation) use ($user) {
                $status = $this->chatService->getConversationWithStatus($conversation, $user);
                
                // Get warehouse manager (admin) using the existing warehouse relationship
                $warehouse = $conversation->warehouse()->with('manager')->first();
                $warehouseAdmin = $warehouse?->manager;

                return [
                    'id' => $conversation->id,
                    'warehouse_id' => $conversation->warehouse_id,
                    'warehouse_name' => $warehouse?->name ?? 'Unknown',
                    'warehouse_admin_name' => $warehouseAdmin?->name,
                    'unread_count' => $status['unread_count'],
                    'latest_message' => $status['latest_message_preview'],
                    'latest_message_sender' => $status['latest_message_sender']?->name,
                    'latest_message_time' => $status['latest_message_time']?->diffForHumans(),
                    'is_super_admin' => $user->isSuperAdmin(),
                ];
            });

        return response()->json([
            'success' => true,
            'conversations' => $conversations,
            'total_unread' => $this->chatService->getTotalUnreadCount($user),
        ]);
    }

    /**
     * Get message history with pagination
     */
    public function getMessages(Request $request, Conversation $conversation)
    {
        // Check multi-warehouse
        ensureMultiWarehouseEnabled();

        $user = auth()->user();

        // Verify access
        if (!$conversation->canAccess($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 50);

        try {
            $messages = $this->chatService->getMessages($conversation, $user, $perPage, $page);

            return response()->json([
                'success' => true,
                'data' => $messages->items(),
                'meta' => [
                    'current_page' => $messages->currentPage(),
                    'per_page' => $messages->perPage(),
                    'total' => $messages->total(),
                    'last_page' => $messages->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Archive conversation for user
     */
    public function archiveConversation(Request $request, Conversation $conversation)
    {
        // Check multi-warehouse
        ensureMultiWarehouseEnabled();

        $user = auth()->user();

        // Verify access
        if (!$conversation->canAccess($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $this->chatService->archiveConversation($conversation, $user);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Mute conversation for user
     */
    public function muteConversation(Request $request, Conversation $conversation)
    {
        // Check multi-warehouse
        ensureMultiWarehouseEnabled();

        $user = auth()->user();

        // Verify access
        if (!$conversation->canAccess($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $this->chatService->muteConversation($conversation, $user);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get unread count for all conversations
     */
    public function getUnreadCount(Request $request)
    {
        // Check multi-warehouse
        ensureMultiWarehouseEnabled();

        $user = auth()->user();
        $totalUnread = $this->chatService->getTotalUnreadCount($user);

        return response()->json([
            'success' => true,
            'total_unread' => $totalUnread,
        ]);
    }
}
