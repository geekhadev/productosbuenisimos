<?php

namespace App\Models\Public;

use App\Enums\ChatbotSource;
use App\Models\Company;
use Database\Factories\Public\ChatbotConversationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'phone',
    'agent_conversation_id',
    'source',
    'is_active',
])]
class ChatbotConversation extends Model
{
    /** @use HasFactory<ChatbotConversationFactory> */
    use HasFactory, HasUuids;

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * @return HasMany<ChatbotMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatbotMessage::class, 'chatbot_conversation_id')
            ->orderBy('created_at');
    }

    protected static function newFactory(): ChatbotConversationFactory
    {
        return ChatbotConversationFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source' => ChatbotSource::class,
            'is_active' => 'boolean',
        ];
    }
}
