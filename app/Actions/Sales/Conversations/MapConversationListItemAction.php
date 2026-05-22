<?php

namespace App\Actions\Sales\Conversations;

use App\Models\Public\ChatbotConversation;

class MapConversationListItemAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(ChatbotConversation $conversation): array
    {
        $customerId = $conversation->getAttribute('lead_customer_id');
        $leadStatus = $conversation->getAttribute('lead_status');
        $customerName = $conversation->getAttribute('customer_full_name');

        return [
            'id' => $conversation->id,
            'phone' => $conversation->phone,
            'contact_name' => $this->contactName($conversation, $customerName),
            'contact_type' => $this->contactType($customerId, $leadStatus),
            'lead_status' => is_string($leadStatus) ? $leadStatus : null,
            'customer_id' => is_string($customerId) ? $customerId : null,
            'source' => $conversation->source->value,
            'last_message_preview' => $conversation->getAttribute('last_message_preview'),
            'last_activity_at' => $conversation->getAttribute('last_activity_at') instanceof \DateTimeInterface
                ? $conversation->getAttribute('last_activity_at')->format(\DateTimeInterface::ATOM)
                : (string) $conversation->getAttribute('last_activity_at'),
        ];
    }

    private function contactName(ChatbotConversation $conversation, mixed $customerName): string
    {
        if (is_string($customerName) && $customerName !== '') {
            return $customerName;
        }

        return $conversation->phone;
    }

    /**
     * @return 'customer'|'lead'|'unknown'
     */
    private function contactType(mixed $customerId, mixed $leadStatus): string
    {
        if (is_string($customerId) && $customerId !== '') {
            return 'customer';
        }

        if (is_string($leadStatus) && $leadStatus !== '') {
            return 'lead';
        }

        return 'unknown';
    }
}
