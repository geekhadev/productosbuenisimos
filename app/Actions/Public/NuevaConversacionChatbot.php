<?php

namespace App\Actions\Public;

use App\Actions\Public\Concerns\EnsuresChatbotLead;
use App\Enums\ChatbotSource;
use App\Models\Public\ChatbotConversation;
use App\Support\ChatbotCompany;
use App\Support\ChatbotPhone;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\ConversationStore;

class NuevaConversacionChatbot
{
    use EnsuresChatbotLead;

    public function __construct(private readonly ConversationStore $conversationStore) {}

    /**
     * @return array{
     *     conversation_id: string,
     *     is_new: bool,
     *     messages: list<array{role: string, source: string, content: string, created_at: string}>
     * }
     */
    public function execute(string $conversationId, ChatbotSource $source): array
    {
        $company = ChatbotCompany::findOrFail();

        $current = ChatbotConversation::query()
            ->where('id', $conversationId)
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->firstOrFail();

        return DB::transaction(function () use ($current, $source): array {
            $current->update(['is_active' => false]);

            $agentConversationId = $this->conversationStore->storeConversation(
                ChatbotPhone::participantUserId($current->phone),
                'Chat de ventas',
            );

            $conversation = ChatbotConversation::query()->create([
                'company_id' => $current->company_id,
                'phone' => $current->phone,
                'agent_conversation_id' => $agentConversationId,
                'source' => $source,
                'is_active' => true,
            ]);

            $this->ensureLeadForPhone($current->company_id, $current->phone, $source);

            return [
                'conversation_id' => $conversation->id,
                'is_new' => true,
                'messages' => [],
            ];
        });
    }
}
