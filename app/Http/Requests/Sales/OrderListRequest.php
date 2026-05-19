<?php

namespace App\Http\Requests\Sales;

use App\Http\Requests\Concerns\InteractsWithPaginatedListQuery;
use App\Models\Sales\Order;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderListRequest extends FormRequest
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
            'sort' => ['nullable', 'string', Rule::in(Order::SORTABLE_COLUMNS)],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([20, 50, 100])],
            'date_from' => ['nullable', 'date', 'date_format:Y-m-d'],
            'date_to' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
                Rule::when(
                    $this->filled('date_from'),
                    ['after_or_equal:date_from'],
                ),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->prepareStandardListQuery();

        foreach (['date_from', 'date_to'] as $key) {
            $value = $this->input($key);
            if (is_string($value) && $value === '') {
                $this->merge([$key => null]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function filtersForAction(): array
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        $filters = $this->standardListFiltersForAction($validated);
        $filters['date_from'] = $this->normalizeQueryDate($validated['date_from'] ?? null);
        $filters['date_to'] = $this->normalizeQueryDate($validated['date_to'] ?? null, endOfDay: true);

        return $filters;
    }

    /**
     * @return array<string, mixed>
     */
    public function filtersForFrontend(): array
    {
        $standard = $this->standardListFiltersForFrontend();

        return array_merge($standard, [
            'date_from' => $this->input('date_from'),
            'date_to' => $this->input('date_to'),
        ]);
    }
}
