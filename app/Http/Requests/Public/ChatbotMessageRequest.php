<?php

namespace App\Http\Requests\Public;

use App\Enums\ChatbotSource;
use App\Support\ChatbotCompany;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChatbotMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = ChatbotCompany::findOrFail()->id;

        return [
            'conversation_id' => [
                'required',
                'uuid',
                Rule::exists('chatbot_conversations', 'id')
                    ->where('company_id', $companyId)
                    ->where('is_active', true),
            ],
            'source' => ['required', 'string', Rule::in(ChatbotSource::values())],
            'message' => ['required', 'string', 'max:1000'],
            'product_context' => ['nullable', 'array'],
            'product_context.id' => ['required_with:product_context', 'uuid'],
            'product_context.name' => ['required_with:product_context', 'string', 'max:255'],
            'product_context.code' => ['required_with:product_context', 'string', 'max:100'],
            'product_context.sku' => ['required_with:product_context', 'string', 'max:100'],
            'product_context.price' => ['required_with:product_context', 'numeric', 'min:0'],
        ];
    }

    public function source(): ChatbotSource
    {
        return ChatbotSource::from($this->validated('source'));
    }

    /**
     * @return array{id: string, name: string, code: string, sku: string, price: float|int|string}|null
     */
    public function productContext(): ?array
    {
        /** @var array{id: string, name: string, code: string, sku: string, price: float|int|string}|null $context */
        $context = $this->validated('product_context');

        return $context;
    }
}
