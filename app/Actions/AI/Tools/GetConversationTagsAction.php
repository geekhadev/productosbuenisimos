<?php

namespace App\Actions\AI\Tools;

use App\Models\Sales\ConversationTag;

class GetConversationTagsAction
{
    /**
     * @return list<array{id: string, name: string, description: string, color: string, sort_order: int}>
     */
    public function execute(string $companyId): array
    {
        return ConversationTag::forCompany($companyId)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'description', 'color', 'sort_order'])
            ->map(fn (ConversationTag $tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
                'description' => $tag->description,
                'color' => $tag->color,
                'sort_order' => $tag->sort_order,
            ])
            ->values()
            ->all();
    }
}
