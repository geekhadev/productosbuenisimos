<?php

namespace App\Http\Requests\Sales;

use App\Enums\ChatbotSource;
use App\Http\Requests\Concerns\InteractsWithPaginatedListQuery;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConversationListRequest extends FormRequest
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
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([25, 50, 100])],
            'source' => ['nullable', 'string', Rule::in(ChatbotSource::values())],
            'contact_type' => ['nullable', 'string', Rule::in(['customer', 'lead', 'unknown'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $perPage = (int) $this->input('per_page', 25);
        $perPage = in_array($perPage, [25, 50, 100], true) ? $perPage : 25;

        $search = $this->input('search');
        $source = $this->input('source');
        $contactType = $this->input('contact_type');

        $this->merge([
            'per_page' => $perPage,
            'search' => is_string($search) && $search === '' ? null : $search,
            'source' => in_array($source, ChatbotSource::values(), true) ? $source : null,
            'contact_type' => in_array($contactType, ['customer', 'lead', 'unknown'], true)
                ? $contactType
                : null,
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
            'search' => $validated['search'] ?? null,
            'page' => isset($validated['page']) ? (int) $validated['page'] : null,
            'per_page' => (int) ($validated['per_page'] ?? 25),
            'source' => $validated['source'] ?? null,
            'contact_type' => $validated['contact_type'] ?? null,
        ];
    }
}
