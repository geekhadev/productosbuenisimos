<?php

namespace Database\Factories\Public;

use App\Enums\ChatbotMessageRole;
use App\Enums\ChatbotSource;
use App\Models\Public\ChatbotConversation;
use App\Models\Public\ChatbotMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatbotMessage>
 */
class ChatbotMessageFactory extends Factory
{
    /**
     * @var class-string<ChatbotMessage>
     */
    protected $model = ChatbotMessage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chatbot_conversation_id' => ChatbotConversation::factory(),
            'role' => ChatbotMessageRole::User,
            'source' => ChatbotSource::Web,
            'content' => fake()->sentence(),
            'created_at' => now(),
        ];
    }
}
