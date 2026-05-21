<?php

namespace App\Http\Requests\Stock;

use App\Enums\Stock\ProductMediaType;
use App\Models\Stock\Product;
use App\Support\SelectedCompanySession;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;

class UploadProductImagesRequest extends FormRequest
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
            'images' => ['required', 'array', 'min:1', 'max:5'],
            'images.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
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
            $incoming = count($this->file('images', []));
            $existing = $product->media()->where('type', ProductMediaType::Image)->count();

            if ($existing + $incoming > 5) {
                $validator->errors()->add(
                    'images',
                    'El producto no puede tener más de 5 imágenes.',
                );
            }
        });
    }

    /**
     * @return list<UploadedFile>
     */
    public function uploadedImages(): array
    {
        return array_values($this->file('images', []));
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'images.max' => 'Puedes subir como máximo 5 imágenes por solicitud.',
            'images.*.image' => 'Cada archivo debe ser una imagen válida.',
            'images.*.mimes' => 'Formatos admitidos: JPG, PNG y WebP.',
            'images.*.max' => 'Cada imagen no puede superar 10 MB.',
        ];
    }
}
