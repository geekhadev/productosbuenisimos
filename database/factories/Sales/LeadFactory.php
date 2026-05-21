<?php

namespace Database\Factories\Sales;

use App\Enums\Sales\LeadSource;
use App\Enums\Sales\LeadStatus;
use App\Models\Company;
use App\Models\Sales\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    /**
     * @var class-string<Lead>
     */
    protected $model = Lead::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'phone' => fake()->unique()->numerify('569########'),
            'source' => fake()->randomElement(LeadSource::cases()),
            'status' => LeadStatus::Nuevo,
            'customer_id' => null,
        ];
    }

    public function converted(): static
    {
        return $this->state(fn (): array => [
            'status' => LeadStatus::Convertido,
        ]);
    }
}
