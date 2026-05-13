<?php

namespace App\Http\Requests\Administration;

use App\Models\Administration\System;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SystemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var System|null $system */
        $system = $this->route('system');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('administration_systems', 'name')->ignore($system),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('administration_systems', 'slug')->ignore($system),
            ],
        ];
    }

    /**
     * @return array{name: string, slug: string}
     */
    public function systemPayload(): array
    {
        /** @var array{name: string, slug: string} */
        return $this->safe()->only(['name', 'slug']);
    }
}
