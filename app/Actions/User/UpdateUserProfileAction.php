<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateUserProfileAction
{
    /**
     * @param  array{name: string, email: string}  $validatedProfileAttributes
     */
    public function execute(
        User $user,
        array $validatedProfileAttributes,
        bool $typeFieldWasSubmitted,
        mixed $submittedType,
    ): User {
        if ($typeFieldWasSubmitted) {
            $incoming = $this->normalizeSubmittedType($submittedType);
            if ($incoming !== $user->type->value) {
                throw ValidationException::withMessages([
                    'type' => __('El tipo de usuario no puede modificarse desde aquí.'),
                ]);
            }
        }

        $user->fill($validatedProfileAttributes);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user->fresh();
    }

    private function normalizeSubmittedType(mixed $submittedType): string
    {
        if ($submittedType === null || $submittedType === '') {
            return '';
        }

        return (string) $submittedType;
    }
}
