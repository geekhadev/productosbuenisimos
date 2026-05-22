<?php

namespace App\Actions\Sales\Conversations;

use App\Enums\Sales\LeadStatus;
use App\Models\Public\ChatbotConversation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class ListConversationsAction
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(string $companyId, array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 25);
        $page = isset($filters['page']) ? (int) $filters['page'] : null;

        $convertidoStatus = LeadStatus::Convertido->value;

        $bestLeadSub = DB::table('sales_leads as matched_lead_candidate')
            ->select('matched_lead_candidate.*')
            ->whereNull('matched_lead_candidate.deleted_at')
            ->whereRaw(
                'matched_lead_candidate.id = (
                    SELECT sl2.id
                    FROM sales_leads sl2
                    WHERE sl2.company_id = matched_lead_candidate.company_id
                      AND sl2.phone = matched_lead_candidate.phone
                      AND sl2.deleted_at IS NULL
                    ORDER BY
                        CASE WHEN sl2.status = ? THEN 0 ELSE 1 END,
                        sl2.created_at DESC
                    LIMIT 1
                )',
                [$convertidoStatus],
            );

        $query = ChatbotConversation::query()
            ->where('chatbot_conversations.company_id', $companyId)
            ->leftJoinSub($bestLeadSub, 'ranked_leads', function (JoinClause $join): void {
                $join->on('ranked_leads.phone', '=', 'chatbot_conversations.phone')
                    ->on('ranked_leads.company_id', '=', 'chatbot_conversations.company_id');
            })
            ->leftJoin(
                'sales_customers as matched_customer',
                'matched_customer.id',
                '=',
                'ranked_leads.customer_id',
            )
            ->select([
                'chatbot_conversations.*',
                DB::raw(
                    'COALESCE(
                        (SELECT MAX(lm.created_at) FROM chatbot_messages lm WHERE lm.chatbot_conversation_id = chatbot_conversations.id),
                        chatbot_conversations.created_at
                    ) as last_activity_at',
                ),
                DB::raw(
                    '(SELECT lm.content FROM chatbot_messages lm
                      WHERE lm.chatbot_conversation_id = chatbot_conversations.id
                      ORDER BY lm.created_at DESC
                      LIMIT 1) as last_message_content',
                ),
                'ranked_leads.status as lead_status',
                'ranked_leads.customer_id as lead_customer_id',
                'matched_customer.full_name as customer_full_name',
            ])
            ->orderByDesc('last_activity_at');

        $this->applyFilters($query, $filters);

        $paginator = $query->paginate(
            $perPage,
            ['*'],
            'page',
            $page,
        )->withQueryString();

        $paginator->getCollection()->transform(function (ChatbotConversation $conversation): ChatbotConversation {
            $conversation->setAttribute(
                'last_message_preview',
                $this->truncatePreview($conversation->getAttribute('last_message_content')),
            );

            return $conversation;
        });

        return $paginator;
    }

    public function pageForConversation(
        string $companyId,
        ChatbotConversation $conversation,
        int $perPage,
    ): int {
        $lastActivity = $conversation->messages()->max('created_at')
            ?? $conversation->created_at;

        $countAhead = ChatbotConversation::query()
            ->where('chatbot_conversations.company_id', $companyId)
            ->where('chatbot_conversations.id', '!=', $conversation->id)
            ->whereRaw(
                'COALESCE(
                    (SELECT MAX(lm.created_at) FROM chatbot_messages lm WHERE lm.chatbot_conversation_id = chatbot_conversations.id),
                    chatbot_conversations.created_at
                ) > ?',
                [$lastActivity],
            )
            ->count();

        return (int) floor($countAhead / $perPage) + 1;
    }

    /**
     * @param  Builder<ChatbotConversation>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $search = $filters['search'] ?? null;

        if (is_string($search) && $search !== '') {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
            $query->where('chatbot_conversations.phone', 'like', $term);
        }

        $source = $filters['source'] ?? null;

        if (is_string($source) && $source !== '') {
            $query->where('chatbot_conversations.source', $source);
        }

        $contactType = $filters['contact_type'] ?? null;

        if ($contactType === 'customer') {
            $query->whereNotNull('ranked_leads.customer_id');
        } elseif ($contactType === 'lead') {
            $query->whereNotNull('ranked_leads.id')
                ->whereNull('ranked_leads.customer_id');
        } elseif ($contactType === 'unknown') {
            $query->whereNull('ranked_leads.id');
        }
    }

    private function truncatePreview(mixed $content): ?string
    {
        if (! is_string($content) || $content === '') {
            return null;
        }

        if (mb_strlen($content) <= 80) {
            return $content;
        }

        return mb_substr($content, 0, 80).'…';
    }
}
