<?php

namespace App\Actions\Sales\Conversations;

use App\Enums\ChatbotMessageRole;
use App\Models\Public\ChatbotConversation;
use App\Models\Public\ChatbotMessage;
use App\Models\Sales\ConversationTag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\Data\ToolResult;

class SyncConversationTagAction
{
    public function execute(ChatbotConversation $conversation, ?AgentResponse $response = null): void
    {
        $conversation->loadMissing('tag');

        $tags = ConversationTag::forCompany($conversation->company_id)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('sort_order');

        if ($tags->isEmpty()) {
            return;
        }

        $currentSortOrder = $conversation->tag?->sort_order ?? -1;
        $targetSortOrder = $this->resolveTargetSortOrder($conversation, $response);

        if ($targetSortOrder <= $currentSortOrder) {
            return;
        }

        $tag = $tags->get($targetSortOrder);

        if ($tag === null) {
            return;
        }

        $conversation->update(['conversation_tag_id' => $tag->id]);
    }

    private function resolveTargetSortOrder(ChatbotConversation $conversation, ?AgentResponse $response): int
    {
        $tools = $this->collectInvokedTools($conversation, $response);
        $messages = $conversation->messages()->orderBy('created_at')->get();

        $sortOrder = ConversationTag::SORT_CONVERSATION_STARTED;

        if ($this->hasTool($tools, 'add_items_to_order')
            || ($this->hasTool($tools, 'get_similar_products') && $this->hasTool($tools, 'create_order'))) {
            $sortOrder = max($sortOrder, ConversationTag::SORT_CROSS_SELL_OFFERED);
        }

        if ($this->hasTool($tools, 'create_order')) {
            $sortOrder = max($sortOrder, ConversationTag::SORT_ORDER_REGISTERED);
        }

        if ($this->assistantAskedThenUserReplied($messages, '¿Es correcto su pedido?')
            || $this->assistantAskedThenUserReplied($messages, 'Nota de pedido')) {
            $sortOrder = max($sortOrder, ConversationTag::SORT_ORDER_CONFIRMED);
        }

        if ($this->hasTool($tools, 'create_customer_address')) {
            $sortOrder = max($sortOrder, ConversationTag::SORT_ADDRESS_COMPLETE);
        }

        if ($this->assistantAskedThenUserReplied($messages, 'estado de México')) {
            $sortOrder = max($sortOrder, ConversationTag::SORT_ADDRESS_IN_PROGRESS);
        }

        if ($this->assistantAskedThenUserReplied($messages, '¿Agregamos el producto a su pedido?')) {
            $sortOrder = max($sortOrder, ConversationTag::SORT_INTEREST_CONFIRMED);
        }

        if ($this->hasProductPresented($messages)) {
            $sortOrder = max($sortOrder, ConversationTag::SORT_PRODUCT_PRESENTED);
        }

        if ($this->hasTool($tools, 'get_products')) {
            $sortOrder = max($sortOrder, ConversationTag::SORT_PRODUCT_CONSULTED);
        }

        if ($this->hasContactStarted($tools, $messages)) {
            $sortOrder = max($sortOrder, ConversationTag::SORT_CONTACT_STARTED);
        }

        return $sortOrder;
    }

    /**
     * @return list<string>
     */
    private function collectInvokedTools(ChatbotConversation $conversation, ?AgentResponse $response): array
    {
        $tools = [];

        $messagesTable = config('ai.conversations.tables.messages', 'agent_conversation_messages');

        $rows = DB::table($messagesTable)
            ->where('conversation_id', $conversation->agent_conversation_id)
            ->where('role', 'assistant')
            ->pluck('tool_calls');

        foreach ($rows as $json) {
            $calls = json_decode($json ?: '[]', true) ?? [];

            foreach ($calls as $call) {
                if (is_array($call) && isset($call['name'])) {
                    $tools[] = (string) $call['name'];
                }
            }
        }

        if ($response !== null) {
            foreach ($response->toolResults as $toolResult) {
                if ($toolResult instanceof ToolResult) {
                    $tools[] = $toolResult->name;
                }
            }
        }

        return array_values(array_unique($tools));
    }

    /**
     * @param  list<string>  $tools
     * @param  Collection<int, ChatbotMessage>  $messages
     */
    private function hasContactStarted(array $tools, Collection $messages): bool
    {
        if ($this->hasTool($tools, 'create_customer')) {
            return true;
        }

        $userMessages = $messages->where('role', ChatbotMessageRole::User);

        if ($userMessages->count() >= 2) {
            return true;
        }

        return $userMessages->count() >= 1
            && $this->assistantAskedThenUserReplied($messages, '¿Con quién tengo el gusto?');
    }

    /**
     * @param  Collection<int, ChatbotMessage>  $messages
     */
    private function hasProductPresented(Collection $messages): bool
    {
        return $messages
            ->where('role', ChatbotMessageRole::Assistant)
            ->contains(function (ChatbotMessage $message): bool {
                foreach ($message->attachments ?? [] as $attachment) {
                    if (is_array($attachment) && ($attachment['type'] ?? '') === 'video') {
                        return true;
                    }
                }

                return false;
            });
    }

    /**
     * @param  Collection<int, ChatbotMessage>  $messages
     */
    private function assistantAskedThenUserReplied(Collection $messages, string $needle): bool
    {
        $foundAssistantPrompt = false;

        foreach ($messages as $message) {
            if ($message->role === ChatbotMessageRole::Assistant && str_contains($message->content, $needle)) {
                $foundAssistantPrompt = true;

                continue;
            }

            if ($foundAssistantPrompt && $message->role === ChatbotMessageRole::User) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $tools
     */
    private function hasTool(array $tools, string $name): bool
    {
        return in_array($name, $tools, true);
    }
}
