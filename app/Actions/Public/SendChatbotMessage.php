<?php

namespace App\Actions\Public;

use App\Actions\Public\Concerns\BuildsChatbotAgentPrompt;
use App\Ai\Agents\SalesAgent;
use App\Enums\ChatbotMessageRole;
use App\Enums\ChatbotSource;
use App\Exceptions\Public\ChatbotAgentUnavailableException;
use App\Models\Public\ChatbotConversation;
use App\Models\Public\ChatbotMessage;
use App\Support\ChatbotAiConfiguration;
use App\Support\ChatbotCompany;
use App\Support\ChatbotParticipant;
use App\Support\ChatbotPhone;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Ai;
use Throwable;

class SendChatbotMessage
{
    use BuildsChatbotAgentPrompt;

    /**
     * @param  array{id: string, name: string, code: string, sku: string, price: float|int|string}|null  $productContext
     * @return array{reply: string}
     */
    public function execute(
        string $conversationId,
        ChatbotSource $source,
        string $message,
        ?array $productContext = null,
    ): array {
        if (! ChatbotAiConfiguration::isConfigured() && ! Ai::hasFakeGatewayFor(SalesAgent::class)) {
            throw ChatbotAgentUnavailableException::notConfigured();
        }

        $company = ChatbotCompany::findOrFail();

        $conversation = ChatbotConversation::query()
            ->where('id', $conversationId)
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->firstOrFail();

        $builtPrompt = $this->buildAgentPrompt(
            message: $message,
            phone: $conversation->phone,
            productContext: $productContext,
        );

        return DB::transaction(function () use ($conversation, $source, $message, $builtPrompt, $company): array {
            ChatbotMessage::query()->create([
                'chatbot_conversation_id' => $conversation->id,
                'role' => ChatbotMessageRole::User,
                'source' => $source,
                'content' => $message,
                'created_at' => now(),
            ]);

            $participant = new ChatbotParticipant(
                ChatbotPhone::participantUserId($conversation->phone),
            );

            try {
                $response = SalesAgent::make(companyId: $company->id)
                    ->continue($conversation->agent_conversation_id, $participant)
                    ->prompt($builtPrompt);
            } catch (Throwable $exception) {
                report($exception);

                throw ChatbotAgentUnavailableException::requestFailed();
            }

            ChatbotMessage::query()->create([
                'chatbot_conversation_id' => $conversation->id,
                'role' => ChatbotMessageRole::Assistant,
                'source' => $source,
                'content' => $response->text,
                'input_tokens' => $response->usage->promptTokens,
                'output_tokens' => $response->usage->completionTokens,
                'created_at' => now(),
            ]);

            return ['reply' => $response->text];
        });
    }
}
