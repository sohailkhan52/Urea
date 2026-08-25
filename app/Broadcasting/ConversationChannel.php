<?php

namespace App\Broadcasting;

use App\Models\Conversation;
use App\Models\User;

class ConversationChannel
{
    /**
     * Authenticate the user's access to the channel.
     *
     * @param \App\Models\User $user
     * @param string $conversationId
     * @return array|bool
     */
    public function join(User $user, $conversationId)
    {
        // Find the conversation
        $conversation = Conversation::findOrFail($conversationId);

        // Check if user can access this conversation
        if (!$conversation->canAccess($user)) {
            return false;
        }

        // Verify user is a participant
        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            return false;
        }

        return true;
    }
}
