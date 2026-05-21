<?php

namespace App\Http\Requests\Public;

use App\Enums\ChatbotSource;
use App\Support\ChatbotCompany;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NuevaConversacionChatbotRequest extends FormRequest
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
        ];
    }

    public function source(): ChatbotSource
    {
        return ChatbotSource::from($this->validated('source'));
    }
}
