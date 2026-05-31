<?php

namespace App\Models\Public;

use App\Enums\ChatbotMessageRole;
use App\Enums\ChatbotSource;
use Database\Factories\Public\ChatbotMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'chatbot_conversation_id',
    'role',
    'source',
    'content',
    'attachments',
    'input_tokens',
    'output_tokens',
])]
class ChatbotMessage extends Model
{
    /** @use HasFactory<ChatbotMessageFactory> */
    use HasFactory, HasUuids;

    public $timestamps = false;

    /**
     * @return BelongsTo<ChatbotConversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatbotConversation::class, 'chatbot_conversation_id');
    }

    protected static function newFactory(): ChatbotMessageFactory
    {
        return ChatbotMessageFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => ChatbotMessageRole::class,
            'source' => ChatbotSource::class,
            'attachments' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
