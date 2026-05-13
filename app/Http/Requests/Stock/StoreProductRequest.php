<?php

namespace App\Http\Requests\Stock;

use App\Models\Stock\Product;
use App\Support\SelectedCompanySession;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $companyId = SelectedCompanySession::selectedCompanyId($this);

        return $companyId !== null && $companyId !== '';
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'code' => [
                'required',
                'string',
                'max:255',
            ],
            'sku' => [
                'required',
                'string',
                'max:255',
            ],
            'width' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'volume' => ['nullable', 'numeric', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $companyId = SelectedCompanySession::selectedCompanyId($this);

            if ($companyId === null || $companyId === '') {
                return;
            }

            $this->assertUniqueAmongActiveAndExplainTrashed($validator, $companyId, 'name', 'nombre');
            $this->assertUniqueAmongActiveAndExplainTrashed($validator, $companyId, 'code', 'código');
            $this->assertUniqueAmongActiveAndExplainTrashed($validator, $companyId, 'sku', 'SKU');
        });
    }

    /**
     * @param  string  $field  Columna: name, code o sku
     */
    private function assertUniqueAmongActiveAndExplainTrashed(
        Validator $validator,
        string $companyId,
        string $field,
        string $labelForMessage,
    ): void {
        if ($validator->errors()->has($field)) {
            return;
        }

        $value = $this->input($field);

        if (! is_string($value) || $value === '') {
            return;
        }

        $trashedExists = Product::onlyTrashed()
            ->where('company_id', $companyId)
            ->where($field, $value)
            ->exists();

        if ($trashedExists) {
            $validator->errors()->add(
                $field,
                "Producto existente y eliminado con este {$labelForMessage}.",
            );

            return;
        }

        $activeExists = Product::query()
            ->where('company_id', $companyId)
            ->where($field, $value)
            ->exists();

        if ($activeExists) {
            $validator->errors()->add(
                $field,
                "Ya existe un producto activo con este {$labelForMessage}.",
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function productPayload(): array
    {
        return $this->validated();
    }
}
