<?php

namespace App\Actions\Sales\Conversations;

use App\Models\Public\ChatbotConversation;
use Illuminate\Support\Facades\DB;

class DeleteConversationAction
{
    public function execute(ChatbotConversation $conversation): void
    {
        $agentConversationId = $conversation->agent_conversation_id;

        $conversation->messages()->delete();
        $conversation->delete();

        if ($agentConversationId) {
            $messagesTable = config('ai.conversations.tables.messages', 'agent_conversation_messages');
            $conversationsTable = config('ai.conversations.tables.conversations', 'agent_conversations');

            DB::table($messagesTable)->where('conversation_id', $agentConversationId)->delete();
            DB::table($conversationsTable)->where('id', $agentConversationId)->delete();
        }
    }
}
