<?php

namespace App\Http\Requests\Configuration;

use App\Enums\UserType;
use App\Http\Requests\Concerns\InteractsWithPaginatedListQuery;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserListRequest extends FormRequest
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
        $rules = [
            'search' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'string', Rule::in(User::SORTABLE_COLUMNS)],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([20, 50, 100])],
        ];

        if ($this->user()?->type === UserType::Root) {
            $rules['type'] = ['nullable', 'string', Rule::in(['root', 'owner', 'user'])];
            $rules['company_id'] = ['nullable', 'uuid', Rule::exists('configuration_companies', 'id')];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareStandardListQuery();

        if ($this->user()?->type !== UserType::Root) {
            return;
        }

        foreach (['type', 'company_id'] as $key) {
            $raw = $this->input($key);
            if ($raw === '' || $raw === null) {
                $this->merge([$key => null]);
            } elseif (is_string($raw)) {
                $this->merge([$key => $raw]);
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
        $base = $this->standardListFiltersForAction($validated);

        if ($this->user()?->type !== UserType::Root) {
            return $base;
        }

        return array_merge($base, [
            'type' => $validated['type'] ?? null,
            'company_id' => $validated['company_id'] ?? null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function filtersForFrontend(): array
    {
        $base = $this->standardListFiltersForFrontend();

        if ($this->user()?->type !== UserType::Root) {
            return $base;
        }

        foreach (['type', 'company_id'] as $key) {
            $raw = $this->input($key);
            $base[$key] = $raw === '' || $raw === null ? null : $raw;
        }

        return $base;
    }
}
