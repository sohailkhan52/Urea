<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\ConversationSetting;
use App\Models\Message;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * ChatService
 * 
 * Handles all chat and messaging operations with proper authorization.
 */
class ChatService
{
    /**
     * Initialize or get warehouse conversation
     */
    public function getOrCreateConversation(Warehouse $warehouse): Conversation
    {
        // Get Super Admin for created_by (conversation created by system)
        $superAdmin = User::whereHas('roles', function ($q) {
            $q->where('is_super_admin', true);
        })->first();

        $createdBy = auth()->id() ?? ($superAdmin?->id ?? 1);

        \Log::info('ChatService::getOrCreateConversation', [
            'warehouse_id' => $warehouse->id,
            'warehouse_name' => $warehouse->name,
            'created_by' => $createdBy,
            'super_admin' => $superAdmin?->name,
        ]);

        $conversation = Conversation::firstOrCreate(
            [
                'warehouse_id' => $warehouse->id,
                'type' => 'warehouse',
            ],
            [
                'created_by' => $createdBy,
            ]
        );

        \Log::info('ChatService::getOrCreateConversation result', [
            'conversation_id' => $conversation->id,
            'warehouse_id' => $conversation->warehouse_id,
        ]);

        return $conversation;
    }

    /**
     * Get conversation if user has access
     */
    public function getAccessibleConversation(User $user, Conversation $conversation): ?Conversation
    {
        if (!$conversation->canAccess($user)) {
            return null;
        }

        return $conversation;
    }

    /**
     * Get all conversations for user
     */
    public function getUserConversations(User $user): Collection
    {
        $conversationIds = Conversation::accessibleBy($user)
            ->leftJoin('messages', 'messages.conversation_id', 'conversations.id')
            ->select('conversations.id')
            ->groupBy('conversations.id')
            ->orderByRaw('MAX(messages.created_at) DESC')
            ->pluck('conversations.id');

        return Conversation::whereIn('id', $conversationIds)
            ->with([
                'warehouse',
                'latestMessage.sender',
                'participants',
                'conversationParticipants' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                },
            ])
            ->orderByRaw('FIELD(id, ' . implode(',', $conversationIds->toArray()) . ')')
            ->get();
    }

    /**
     * Add participant to conversation
     */
    public function addParticipant(Conversation $conversation, User $user): ConversationParticipant
    {
        return ConversationParticipant::firstOrCreate(
            [
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
            ],
            [
                'joined_at' => now(),
            ]
        );
    }

    /**
     * Get or create conversation setting for user
     */
    public function getOrCreateSetting(Conversation $conversation, User $user): ConversationSetting
    {
        return ConversationSetting::firstOrCreate(
            [
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
            ],
            [
                'notifications_enabled' => true,
                'archived' => false,
                'muted' => false,
            ]
        );
    }

    /**
     * Send a message
     */
    public function sendMessage(
        User $sender,
        Conversation $conversation,
        string $message,
        string $messageType = 'text',
        ?string $relatedType = null,
        ?int $relatedId = null
    ): Message {
        // Verify sender is participant
        if (!$conversation->participants()->where('user_id', $sender->id)->exists()) {
            throw new \Exception('Sender is not a participant of this conversation.');
        }

        // Verify sender can access conversation
        if (!$conversation->canAccess($sender)) {
            throw new \Exception('Sender does not have access to this conversation.');
        }

        return DB::transaction(function () use (
            $sender,
            $conversation,
            $message,
            $messageType,
            $relatedType,
            $relatedId
        ) {
            $msg = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'message' => $message,
                'message_type' => $messageType,
                'related_type' => $relatedType,
                'related_id' => $relatedId,
            ]);

            // Mark as read by sender
            $msg->markAsReadBy($sender);

            // Update conversation's updated_at
            $conversation->touch();

            return $msg->load('sender', 'reads');
        });
    }

    /**
     * Get messages for conversation with pagination
     */
    public function getMessages(Conversation $conversation, User $user, int $perPage = 50, int $page = 1)
    {
        if (!$conversation->canAccess($user)) {
            throw new \Exception('User does not have access to this conversation.');
        }

        return $conversation->messages()
            ->with('sender', 'reads')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Mark conversation messages as read
     */
    public function markAsRead(Conversation $conversation, User $user, ?Message $upToMessage = null): void
    {
        if (!$conversation->canAccess($user)) {
            throw new \Exception('User does not have access to this conversation.');
        }

        $conversation->markAsRead($user, $upToMessage);
    }

    /**
     * Get unread count for all conversations of user
     */
    public function getTotalUnreadCount(User $user): int
    {
        return DB::table('conversation_participants')
            ->join('conversations', 'conversation_participants.conversation_id', 'conversations.id')
            ->join('warehouses', 'conversations.warehouse_id', 'warehouses.id')
            ->where('conversation_participants.user_id', $user->id)
            ->where('warehouses.status', 'active')
            ->select('conversation_participants.conversation_id')
            ->distinct()
            ->get()
            ->reduce(function ($count, $row) use ($user) {
                $conversation = Conversation::find($row->conversation_id);
                return $count + $conversation->unreadCount($user);
            }, 0);
    }

    /**
     * Ensure warehouse has participants
     * 
     * This method:
     * 1. Creates conversation if it doesn't exist
     * 2. Adds Super Admin as participant
     * 3. Adds Regular Admins assigned to this warehouse
     * 4. Creates conversation settings for each participant
     */
    public function ensureWarehouseParticipants(Warehouse $warehouse): void
    {
        $conversation = $this->getOrCreateConversation($warehouse);

        // Add Super Admin - Query using role relationship
        $superAdmins = User::whereHas('roles', function ($q) {
            $q->where('is_super_admin', true);
        })->get();

        foreach ($superAdmins as $superAdmin) {
            $this->addParticipant($conversation, $superAdmin);
            $this->getOrCreateSetting($conversation, $superAdmin);
        }

        // Add warehouse manager (manager_id) if set
        if ($warehouse->manager_id) {
            $manager = User::find($warehouse->manager_id);
            if ($manager && !$manager->isSuperAdmin()) {
                $this->addParticipant($conversation, $manager);
                $this->getOrCreateSetting($conversation, $manager);
            }
        }

        // Add assigned admins via user_warehouse_assignments relationship
        $assignedAdmins = $warehouse->admins()->get();
        foreach ($assignedAdmins as $admin) {
            if (!$admin->isSuperAdmin()) {
                $this->addParticipant($conversation, $admin);
                $this->getOrCreateSetting($conversation, $admin);
            }
        }
    }

    /**
     * Update admin for warehouse (add/remove from conversation)
     */
    public function updateWarehouseAdmin(Warehouse $warehouse, ?User $newAdmin = null): void
    {
        $conversation = $this->getOrCreateConversation($warehouse);

        // Get current assigned admins via the relationship
        $currentAdmins = $warehouse->admins()->pluck('user_id')->toArray();

        // Remove all current admins from conversation
        foreach ($currentAdmins as $adminId) {
            $conversation->participants()->detach($adminId);
            ConversationSetting::where('conversation_id', $conversation->id)
                ->where('user_id', $adminId)
                ->delete();
        }

        // Re-initialize all current admins (will add from the relationship)
        $this->ensureWarehouseParticipants($warehouse);
    }

    /**
     * Archive conversation for user
     */
    public function archiveConversation(Conversation $conversation, User $user): void
    {
        $this->getOrCreateSetting($conversation, $user)
            ->update(['archived' => true]);
    }

    /**
     * Unarchive conversation for user
     */
    public function unarchiveConversation(Conversation $conversation, User $user): void
    {
        $this->getOrCreateSetting($conversation, $user)
            ->update(['archived' => false]);
    }

    /**
     * Mute conversation for user
     */
    public function muteConversation(Conversation $conversation, User $user): void
    {
        $this->getOrCreateSetting($conversation, $user)
            ->update(['muted' => true]);
    }

    /**
     * Unmute conversation for user
     */
    public function unmuteConversation(Conversation $conversation, User $user): void
    {
        $this->getOrCreateSetting($conversation, $user)
            ->update(['muted' => false]);
    }

    /**
     * Get conversation with latest unread info
     */
    public function getConversationWithStatus(Conversation $conversation, User $user): array
    {
        if (!$conversation->canAccess($user)) {
            throw new \Exception('User does not have access to this conversation.');
        }

        $unreadCount = $conversation->unreadCount($user);
        $latestMessage = $conversation->latestMessage()->first();

        return [
            'conversation' => $conversation,
            'unread_count' => $unreadCount,
            'latest_message' => $latestMessage,
            'latest_message_preview' => $latestMessage 
                ? (strlen($latestMessage->message) > 50 
                    ? substr($latestMessage->message, 0, 50) . '...' 
                    : $latestMessage->message)
                : null,
            'latest_message_sender' => $latestMessage?->sender,
            'latest_message_time' => $latestMessage?->created_at,
        ];
    }

    /**
     * Delete old messages (for cleanup)
     */
    public function deleteOldMessages(int $daysOld = 90): int
    {
        $before = now()->subDays($daysOld);

        return Message::where('created_at', '<', $before)->delete();
    }

    /**
     * Get conversation stats
     */
    public function getConversationStats(Conversation $conversation): array
    {
        return [
            'total_messages' => $conversation->messages()->count(),
            'total_participants' => $conversation->participants()->count(),
            'created_at' => $conversation->created_at,
            'last_message_at' => $conversation->latestMessage()->first()?->created_at,
        ];
    }
}
