<?php

namespace App\Http\Requests\Configuration;

use App\Support\AiConfigurationBridge;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAiDefaultProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'provider' => [
                'required',
                'string',
                Rule::in(AiConfigurationBridge::selectableDefaultProviderSlugs()),
            ],
        ];
    }

    public function provider(): string
    {
        return (string) $this->validated('provider');
    }
}
