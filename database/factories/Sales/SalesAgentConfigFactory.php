<?php

namespace Database\Factories\Sales;

use App\Models\Company;
use App\Models\Sales\SalesAgentConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesAgentConfig>
 */
class SalesAgentConfigFactory extends Factory
{
    protected $model = SalesAgentConfig::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'enabled_tools' => SalesAgentConfig::defaultEnabledTools(),
            'provider' => SalesAgentConfig::defaultProvider(),
            'model' => null,
            'prompt' => null,
        ];
    }

    /**
     * @param  list<string>  $enabledTools
     */
    public function withEnabledTools(array $enabledTools): static
    {
        return $this->state(fn (array $attributes): array => [
            'enabled_tools' => $enabledTools,
        ]);
    }

    public function withCustomPrompt(string $prompt): static
    {
        return $this->state(fn (array $attributes): array => [
            'prompt' => $prompt,
        ]);
    }
}
