<?php

namespace Database\Factories\Public;

use App\Enums\ChatbotSource;
use App\Models\Company;
use App\Models\Public\ChatbotConversation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ChatbotConversation>
 */
class ChatbotConversationFactory extends Factory
{
    /**
     * @var class-string<ChatbotConversation>
     */
    protected $model = ChatbotConversation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'phone' => fake()->numerify('9########'),
            'agent_conversation_id' => (string) Str::uuid7(),
            'source' => ChatbotSource::Web,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
