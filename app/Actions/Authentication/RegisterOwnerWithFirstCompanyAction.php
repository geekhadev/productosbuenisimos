<?php

namespace App\Actions\Authentication;

use App\Actions\Configuration\Companies\CreateCompanyAction;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserType;
use App\Models\User;
use App\Support\UserNonRootRequiresCompanyPivot;
use App\Support\Validation\CompanyPayloadValidationRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RegisterOwnerWithFirstCompanyAction
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(
        private CreateCompanyAction $createCompany,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function execute(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        if (! is_array($input['company'] ?? null)) {
            throw ValidationException::withMessages([
                'company' => __('Debes indicar los datos de tu empresa.'),
            ]);
        }

        $input['company'] = CompanyPayloadValidationRules::mergeNormalizedNullableCompanyFragment($input['company']);

        return DB::transaction(function () use ($input): User {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'type' => UserType::Owner,
                'password' => $input['password'],
            ]);

            $validated = Validator::make(
                $input,
                CompanyPayloadValidationRules::rules(null, $input, 'company'),
            )->validate();

            /** @var array{document_type: string, document_number: string, name: string, alias: string|null, email: string|null, phone: string|null, address: string|null} $payload */
            $payload = array_merge(
                [
                    'alias' => null,
                    'email' => null,
                    'phone' => null,
                    'address' => null,
                ],
                $validated['company'],
            );

            $this->createCompany->execute($user, $payload);

            $user->refresh();

            UserNonRootRequiresCompanyPivot::assertSatisfied($user);

            return $user;
        });
    }
}
