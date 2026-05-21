<?php

namespace App\Http\Requests\Public;

use App\Enums\ChatbotSource;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IniciarChatbotRequest extends FormRequest
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
        return [
            'phone' => ['required', 'string', 'min:7', 'max:20'],
            'source' => ['required', 'string', Rule::in(ChatbotSource::values())],
        ];
    }

    public function source(): ChatbotSource
    {
        return ChatbotSource::from($this->validated('source'));
    }
}
