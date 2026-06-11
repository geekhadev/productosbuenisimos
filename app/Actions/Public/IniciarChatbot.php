<?php

namespace App\Actions\Public;

use App\Actions\Public\Concerns\EnsuresChatbotLead;
use App\Actions\Sales\Conversations\SyncConversationTagAction;
use App\Enums\ChatbotSource;
use App\Models\Public\ChatbotConversation;
use App\Support\Chatbot\ChatbotMessagePayload;
use App\Support\ChatbotCompany;
use App\Support\ChatbotPhone;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\ConversationStore;

class IniciarChatbot
{
    use EnsuresChatbotLead;

    public function __construct(
        private readonly ConversationStore $conversationStore,
        private readonly SyncConversationTagAction $syncConversationTag,
    ) {}

    /**
     * @return array{
     *     conversation_id: string,
     *     is_new: bool,
     *     messages: list<array{role: string, source: string, content: string, attachments: list<array<string, mixed>>, created_at: string}>
     * }
     */
    public function execute(string $phone, ChatbotSource $source): array
    {
        $company = ChatbotCompany::findOrFail();
        $normalizedPhone = ChatbotPhone::normalize($phone);

        $existing = ChatbotConversation::query()
            ->where('company_id', $company->id)
            ->where('phone', $normalizedPhone)
            ->where('is_active', true)
            ->first();

        if ($existing !== null) {
            $this->ensureLeadForPhone($company->id, $normalizedPhone, $source);

            return [
                'conversation_id' => $existing->id,
                'is_new' => false,
                'messages' => $this->formatMessages($existing),
            ];
        }

        return DB::transaction(function () use ($company, $normalizedPhone, $source): array {
            $agentConversationId = $this->conversationStore->storeConversation(
                ChatbotPhone::participantUserId($normalizedPhone),
                'Chat de ventas',
            );

            $conversation = ChatbotConversation::query()->create([
                'company_id' => $company->id,
                'phone' => $normalizedPhone,
                'agent_conversation_id' => $agentConversationId,
                'source' => $source,
                'is_active' => true,
            ]);

            $this->ensureLeadForPhone($company->id, $normalizedPhone, $source);

            $this->syncConversationTag->execute($conversation->fresh(['messages', 'tag']));

            return [
                'conversation_id' => $conversation->id,
                'is_new' => true,
                'messages' => [],
            ];
        });
    }

    /**
     * @return list<array{role: string, source: string, content: string, created_at: string}>
     */
    private function formatMessages(ChatbotConversation $conversation): array
    {
        return $conversation->messages()
            ->get()
            ->map(fn ($message) => ChatbotMessagePayload::format($message))
            ->values()
            ->all();
    }
}
