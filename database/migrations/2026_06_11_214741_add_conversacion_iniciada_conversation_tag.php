<?php

use App\Models\Company;
use App\Models\Public\ChatbotConversation;
use App\Models\Sales\ConversationTag;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Company::query()->each(function (Company $company): void {
            ConversationTag::query()->firstOrCreate(
                [
                    'company_id' => $company->id,
                    'sort_order' => ConversationTag::SORT_CONVERSATION_STARTED,
                ],
                [
                    'name' => 'Conversación iniciada',
                    'description' => 'El cliente abrió el chat y la conversación quedó activa.',
                    'color' => 'zinc-500',
                ],
            );
        });

        ConversationTag::query()
            ->where('sort_order', ConversationTag::SORT_CONVERSATION_STARTED)
            ->each(function (ConversationTag $tag): void {
                ChatbotConversation::query()
                    ->where('company_id', $tag->company_id)
                    ->whereNull('conversation_tag_id')
                    ->update(['conversation_tag_id' => $tag->id]);
            });
    }

    public function down(): void
    {
        ConversationTag::query()
            ->where('sort_order', ConversationTag::SORT_CONVERSATION_STARTED)
            ->where('name', 'Conversación iniciada')
            ->delete();
    }
};
