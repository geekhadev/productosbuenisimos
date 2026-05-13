<?php

namespace App\Support\Validation;

use App\Enums\CompanyDocumentType;
use App\Models\Company;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

final class CompanyPayloadValidationRules
{
    /**
     * @param  array<string, mixed>  $data  Datos del request (puede incluir `company.*` cuando $keyPrefix es `company`).
     * @return array<string, ValidationRule|array<int, mixed|string>|string>
     */
    public static function rules(?Company $company, array $data, string $keyPrefix = ''): array
    {
        if ($company !== null) {
            return [
                self::field($keyPrefix, 'document_type') => ['prohibited'],
                self::field($keyPrefix, 'document_number') => ['prohibited'],
                self::field($keyPrefix, 'name') => ['required', 'string', 'max:255'],
                self::field($keyPrefix, 'alias') => ['nullable', 'string', 'max:255'],
                self::field($keyPrefix, 'email') => ['nullable', 'string', 'email', 'max:255'],
                self::field($keyPrefix, 'phone') => ['nullable', 'string', 'max:255'],
                self::field($keyPrefix, 'address') => ['nullable', 'string', 'max:255'],
            ];
        }

        $tipo = (string) data_get($data, self::field($keyPrefix, 'document_type'));

        return [
            self::field($keyPrefix, 'document_type') => ['required', Rule::enum(CompanyDocumentType::class)],
            self::field($keyPrefix, 'document_number') => [
                'required',
                'string',
                'max:255',
                Rule::unique('configuration_companies', 'document_number')
                    ->where(fn ($query) => $query->where('document_type', $tipo))
                    ->ignore($company),
            ],
            self::field($keyPrefix, 'name') => ['required', 'string', 'max:255'],
            self::field($keyPrefix, 'alias') => ['nullable', 'string', 'max:255'],
            self::field($keyPrefix, 'email') => ['nullable', 'string', 'email', 'max:255'],
            self::field($keyPrefix, 'phone') => ['nullable', 'string', 'max:255'],
            self::field($keyPrefix, 'address') => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function mergeNormalizedNullableFields(array $input): array
    {
        $nullable = ['alias', 'email', 'phone', 'address'];
        $merged = $input;

        foreach ($nullable as $field) {
            $value = $merged[$field] ?? null;
            $merged[$field] = ($value === null || $value === '') ? null : $value;
        }

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $companyInput  Fragmento bajo la clave `company` (sin anidar otra vez).
     * @return array<string, mixed>
     */
    public static function mergeNormalizedNullableCompanyFragment(array $companyInput): array
    {
        return self::mergeNormalizedNullableFields($companyInput);
    }

    public static function field(string $keyPrefix, string $field): string
    {
        return $keyPrefix === '' ? $field : "{$keyPrefix}.{$field}";
    }
}
