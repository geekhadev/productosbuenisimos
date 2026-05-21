<?php

namespace App\Http\Requests\Sales;

use App\Enums\Sales\LeadSource;
use App\Enums\Sales\LeadStatus;
use App\Http\Requests\Concerns\InteractsWithPaginatedListQuery;
use App\Models\Sales\Lead;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadListRequest extends FormRequest
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
            'sort' => ['nullable', 'string', Rule::in(Lead::SORTABLE_COLUMNS)],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([20, 50, 100])],
            'status' => ['nullable', 'string', Rule::in(LeadStatus::values())],
            'source' => ['nullable', 'string', Rule::in(LeadSource::values())],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->prepareStandardListQuery();

        $status = $this->input('status');
        $source = $this->input('source');

        $this->merge([
            'sort' => $this->input('sort', 'created_at'),
            'direction' => $this->input('direction', 'desc'),
            'status' => in_array($status, LeadStatus::values(), true) ? $status : null,
            'source' => in_array($source, LeadSource::values(), true) ? $source : null,
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
            'source' => $validated['source'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filtersForFrontend(): array
    {
        $status = $this->input('status');
        $source = $this->input('source');

        return [
            ...$this->standardListFiltersForFrontend(),
            'status' => in_array($status, LeadStatus::values(), true) ? (string) $status : null,
            'source' => in_array($source, LeadSource::values(), true) ? (string) $source : null,
        ];
    }
}
