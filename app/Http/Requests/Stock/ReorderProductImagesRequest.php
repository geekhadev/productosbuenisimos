<?php

namespace App\Http\Requests\Stock;

use App\Enums\Stock\ProductMediaType;
use App\Models\Stock\Product;
use App\Support\SelectedCompanySession;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReorderProductImagesRequest extends FormRequest
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
        /** @var Product $product */
        $product = $this->route('product');

        return [
            'order' => ['required', 'array', 'min:1'],
            'order.*' => [
                'required',
                'uuid',
                Rule::exists('stock_product_media', 'id')
                    ->where('product_id', $product->id)
                    ->where('type', ProductMediaType::Image->value),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Product $product */
            $product = $this->route('product');
            $imageCount = $product->media()->where('type', ProductMediaType::Image)->count();
            $order = $this->input('order', []);

            if (count($order) !== $imageCount) {
                $validator->errors()->add(
                    'order',
                    'Debes enviar el identificador de todas las imágenes del producto.',
                );
            }
        });
    }

    /**
     * @return list<string>
     */
    public function orderedIds(): array
    {
        /** @var list<string> */
        return array_values($this->validated('order'));
    }
}
