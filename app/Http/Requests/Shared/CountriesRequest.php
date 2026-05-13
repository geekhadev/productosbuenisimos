<?php

namespace App\Http\Requests\Shared;

use App\Models\Shared\Country;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CountriesRequest extends FormRequest
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
        /** @var Country|null $country */
        $country = $this->route('country');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('shared_countries', 'name')->ignore($country),
            ],
            'name_code' => [
                'required',
                'string',
                'max:32',
                Rule::unique('shared_countries', 'name_code')->ignore($country),
            ],
            'phone_code' => ['required', 'string', 'max:32'],
            'currency_name' => ['required', 'string', 'max:255'],
            'currency_symbol' => ['required', 'string', 'max:32'],
        ];
    }

    /**
     * @return array{name: string, name_code: string, phone_code: string, currency_name: string, currency_symbol: string}
     */
    public function countryPayload(): array
    {
        /** @var array{name: string, name_code: string, phone_code: string, currency_name: string, currency_symbol: string} */
        return $this->safe()->only([
            'name',
            'name_code',
            'phone_code',
            'currency_name',
            'currency_symbol',
        ]);
    }
}
