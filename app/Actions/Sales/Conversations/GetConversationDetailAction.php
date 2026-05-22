<?php

namespace App\Actions\Sales\Conversations;

use App\Enums\Sales\LeadStatus;
use App\Models\Public\ChatbotConversation;
use App\Models\Public\ChatbotMessage;
use App\Models\Sales\Lead;

class GetConversationDetailAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(ChatbotConversation $conversation): array
    {
        $conversation->load([
            'messages' => fn ($query) => $query->orderBy('created_at'),
        ]);

        $lead = $this->resolveLead($conversation);

        return [
            'id' => $conversation->id,
            'phone' => $conversation->phone,
            'contact_name' => $this->contactName($conversation, $lead),
            'contact_type' => $this->contactType($lead),
            'lead_status' => $lead?->status?->value,
            'customer_id' => $lead?->customer_id,
            'source' => $conversation->source->value,
            'messages' => $conversation->messages
                ->map(fn (ChatbotMessage $message): array => [
                    'id' => $message->id,
                    'role' => $message->role->value,
                    'source' => $message->source->value,
                    'content' => $message->content,
                    'input_tokens' => $message->input_tokens,
                    'output_tokens' => $message->output_tokens,
                    'created_at' => $message->created_at?->toIso8601String(),
                ])
                ->values()
                ->all(),
            'total_input_tokens' => $conversation->messages->sum('input_tokens'),
            'total_output_tokens' => $conversation->messages->sum('output_tokens'),
        ];
    }

    private function resolveLead(ChatbotConversation $conversation): ?Lead
    {
        return Lead::query()
            ->where('company_id', $conversation->company_id)
            ->where('phone', $conversation->phone)
            ->with('customer')
            ->orderByRaw(
                'CASE WHEN status = ? THEN 0 ELSE 1 END',
                [LeadStatus::Convertido->value],
            )
            ->orderByDesc('created_at')
            ->first();
    }

    private function contactName(ChatbotConversation $conversation, ?Lead $lead): string
    {
        $customerName = $lead?->customer?->full_name;

        if (is_string($customerName) && $customerName !== '') {
            return $customerName;
        }

        return $conversation->phone;
    }

    /**
     * @return 'customer'|'lead'|'unknown'
     */
    private function contactType(?Lead $lead): string
    {
        if ($lead === null) {
            return 'unknown';
        }

        if ($lead->customer_id !== null) {
            return 'customer';
        }

        return 'lead';
    }
}
