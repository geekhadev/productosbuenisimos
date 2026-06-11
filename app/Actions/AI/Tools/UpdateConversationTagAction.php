<?php

namespace App\Actions\AI\Tools;

use App\Models\Public\ChatbotConversation;
use App\Models\Sales\ConversationTag;

class UpdateConversationTagAction
{
    public function execute(string $chatbotConversationId, string $tagId, string $companyId): bool
    {
        $tag = ConversationTag::query()
            ->where('id', $tagId)
            ->where('company_id', $companyId)
            ->first();

        if ($tag === null) {
            return false;
        }

        return (bool) ChatbotConversation::query()
            ->where('id', $chatbotConversationId)
            ->where('company_id', $companyId)
            ->update(['conversation_tag_id' => $tag->id]);
    }
}
