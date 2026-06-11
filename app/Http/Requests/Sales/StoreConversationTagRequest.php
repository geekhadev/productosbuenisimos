<?php

namespace App\Http\Requests\Sales;

use App\Support\SelectedCompanySession;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConversationTagRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:500'],
            'color' => ['required', 'string', 'max:30'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ];
    }

    /**
     * @return array{name: string, description: string, color: string, sort_order: int}
     */
    public function tagPayload(): array
    {
        $validated = $this->validated();

        return [
            'name' => (string) $validated['name'],
            'description' => (string) $validated['description'],
            'color' => (string) $validated['color'],
            'sort_order' => (int) $validated['sort_order'],
        ];
    }
}
