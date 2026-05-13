<?php

namespace App\Http\Requests\Stock;

use App\Http\Requests\Concerns\InteractsWithPaginatedListQuery;
use App\Models\Stock\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductListRequest extends FormRequest
{
    use InteractsWithPaginatedListQuery;

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
            'search' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'string', Rule::in(Product::SORTABLE_COLUMNS)],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([20, 50, 100])],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->prepareStandardListQuery();

        $status = $this->input('status');

        $this->merge([
            'sort' => $this->input('sort', 'name'),
            'direction' => $this->input('direction', 'asc'),
            'status' => in_array($status, ['active', 'inactive'], true) ? $status : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function filtersForAction(): array
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return [
            ...$this->standardListFiltersForAction($validated),
            'status' => $validated['status'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filtersForFrontend(): array
    {
        $status = $this->input('status');

        return [
            ...$this->standardListFiltersForFrontend(),
            'status' => $status === 'active' || $status === 'inactive' ? (string) $status : null,
        ];
    }
}
