<?php

namespace App\Http\Requests\Configuration;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class DestroyUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User && $this->user()?->can('delete', $user) === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $target = $this->route('user');

            if (! $target instanceof User) {
                return;
            }

            if ($target->isSoleOwnerOfAnyCompany()) {
                $v->errors()->add(
                    'user',
                    __('user.configuration_user_delete_blocked_sole_owner'),
                );
            }
        });
    }
}
