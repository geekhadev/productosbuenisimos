<?php

namespace App\Http\Requests\Configuration;

use App\Models\Configuration\WhatsappProviderCredential;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWhatsappProviderCredentialRequest extends FormRequest
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
        $provider = (string) $this->route('provider');
        $hasStoredCredentials = WhatsappProviderCredential::findForProvider($provider)
            ?->hasStoredCredentials() ?? false;

        $fieldDefinitions = WhatsappProviderCredential::fieldDefinitionsForProvider($provider);
        $rules = [];

        foreach ($fieldDefinitions as $fieldKey => $definition) {
            $rules[$fieldKey] = match ($definition['type']) {
                'secret' => [$hasStoredCredentials ? 'nullable' : 'required', 'string', 'min:1', 'max:2048'],
                default => ['required', 'string', 'max:2048'],
            };
        }

        return $rules;
    }

    /**
     * @return array<string, ?string>
     */
    public function credentials(): array
    {
        $provider = (string) $this->route('provider');
        $fieldDefinitions = WhatsappProviderCredential::fieldDefinitionsForProvider($provider);
        $credentials = [];

        foreach (array_keys($fieldDefinitions) as $fieldKey) {
            $value = $this->validated($fieldKey);
            $credentials[$fieldKey] = is_string($value) && $value !== '' ? trim($value) : null;
        }

        return $credentials;
    }
}
