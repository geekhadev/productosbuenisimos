<?php

namespace Database\Factories;

use App\Enums\CompanyDocumentType;
use App\Models\Company;
use App\Models\Configuration\Role;
use App\Models\User;
use App\Models\UserCompanyRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * @var class-string<Company>
     */
    protected $model = Company::class;

    /**
     * @return $this
     */
    public function ownedBy(User $user): static
    {
        return $this->afterCreating(function (Company $company) use ($user): void {
            $roleId = Role::query()->systemOwner()->value('id');

            if ($roleId === null) {
                throw new \RuntimeException('Public Owner role is missing; run migrations.');
            }

            UserCompanyRole::query()->create([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'role_id' => $roleId,
            ]);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tipo = fake()->randomElement(CompanyDocumentType::cases());

        return [
            'document_type' => $tipo,
            'document_number' => fake()->unique()->numerify('########-#'),
            'name' => fake()->unique()->company(),
            'alias' => fake()->unique()->lexify('????????'),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
        ];
    }
}
