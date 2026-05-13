<?php

namespace App\Http\Requests\Settings;

use App\Actions\User\UpdateUserProfileAction;
use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Actualización de perfil: solo `name` y `email`. El `type` no es editable por este flujo.
     *
     * @see UpdateUserProfileAction
     *
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->profileRules($this->user()->id);
    }
}
