<?php

namespace App\Http\Requests\Stock;

use App\Models\Stock\Product;
use App\Support\SelectedCompanySession;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
        $companyId = SelectedCompanySession::selectedCompanyId($this);
        /** @var Product $product */
        $product = $this->route('product');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('stock_products', 'name')
                    ->where('company_id', $companyId)
                    ->ignore($product->id),
            ],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('stock_products', 'code')
                    ->where('company_id', $companyId)
                    ->ignore($product->id),
            ],
            'sku' => [
                'required',
                'string',
                'max:255',
                Rule::unique('stock_products', 'sku')
                    ->where('company_id', $companyId)
                    ->ignore($product->id),
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

    /**
     * @return array<string, mixed>
     */
    public function productPayload(): array
    {
        return $this->validated();
    }
}
