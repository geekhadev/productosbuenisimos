<?php

namespace App\Http\Requests\Configuration;

use App\Models\Configuration\FulfillmentProviderCredential;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFulfillmentProviderCredentialRequest extends FormRequest
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
        $hasStoredCredentials = FulfillmentProviderCredential::findForProvider($provider)
            ?->hasStoredCredentials() ?? false;

        return [
            'api_url' => ['required', 'string', 'url', 'max:2048'],
            'user' => ['required', 'string', 'max:255'],
            'pass' => [$hasStoredCredentials ? 'nullable' : 'required', 'string', 'min:1', 'max:2048'],
        ];
    }

    /**
     * @return array{api_url: string, user: string, pass: ?string}
     */
    public function credentials(): array
    {
        $pass = $this->validated('pass');

        return [
            'api_url' => trim((string) $this->validated('api_url')),
            'user' => trim((string) $this->validated('user')),
            'pass' => is_string($pass) && $pass !== '' ? $pass : null,
        ];
    }
}
