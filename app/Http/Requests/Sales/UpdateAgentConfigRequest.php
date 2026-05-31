<?php

namespace App\Http\Requests\Sales;

use App\Models\Sales\SalesAgentConfig;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAgentConfigRequest extends FormRequest
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
        return [
            'enabled_tools' => ['required', 'array', 'min:1'],
            'enabled_tools.*' => ['required', 'string', Rule::in(SalesAgentConfig::TOOL_SLUGS)],
            'provider' => ['required', 'string', Rule::in(SalesAgentConfig::allowedProviders())],
            'model' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $provider = (string) $this->input('provider', '');
                    $allowedModels = SalesAgentConfig::allowedModelsForProvider($provider);

                    if (! is_string($value) || ! in_array($value, $allowedModels, true)) {
                        $fail('El modelo seleccionado no es válido para el proveedor.');
                    }
                },
            ],
            'prompt' => ['nullable', 'string', 'max:10000'],
            'use_default_prompt' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array{
     *     enabled_tools: list<string>,
     *     provider: string,
     *     model: string,
     *     prompt: ?string,
     * }
     */
    public function configPayload(): array
    {
        $validated = $this->validated();
        $useDefaultPrompt = (bool) ($validated['use_default_prompt'] ?? false);
        $prompt = $validated['prompt'] ?? null;

        if ($useDefaultPrompt) {
            $prompt = null;
        } elseif (is_string($prompt) && trim($prompt) === '') {
            $prompt = null;
        }

        return [
            'enabled_tools' => SalesAgentConfig::defaultEnabledTools(),
            'provider' => $validated['provider'],
            'model' => $validated['model'],
            'prompt' => $prompt,
        ];
    }
}
