<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Proveedores administrables desde Configuración
    |--------------------------------------------------------------------------
    |
    | Catálogo de proveedores de IA que se pueden configurar en el panel.
    | Las claves de cada campo deben coincidir con config/ai.php → providers.*.
    |
    */

    'providers' => [
        'openai' => [
            'label' => 'OpenAI',
            'fields' => [
                'key' => ['label' => 'API Key', 'type' => 'secret'],
            ],
        ],
        'anthropic' => [
            'label' => 'Anthropic',
            'fields' => [
                'key' => ['label' => 'API Key', 'type' => 'secret'],
            ],
        ],
        'gemini' => [
            'label' => 'Google Gemini',
            'fields' => [
                'key' => ['label' => 'API Key', 'type' => 'secret'],
            ],
        ],
        'groq' => [
            'label' => 'Groq',
            'fields' => [
                'key' => ['label' => 'API Key', 'type' => 'secret'],
            ],
        ],
        'mistral' => [
            'label' => 'Mistral',
            'fields' => [
                'key' => ['label' => 'API Key', 'type' => 'secret'],
            ],
        ],
        'deepseek' => [
            'label' => 'DeepSeek',
            'fields' => [
                'key' => ['label' => 'API Key', 'type' => 'secret'],
            ],
        ],
        'cohere' => [
            'label' => 'Cohere',
            'fields' => [
                'key' => ['label' => 'API Key', 'type' => 'secret'],
            ],
        ],
        'openrouter' => [
            'label' => 'OpenRouter',
            'fields' => [
                'key' => ['label' => 'API Key', 'type' => 'secret'],
            ],
        ],
        'ollama' => [
            'label' => 'Ollama',
            'fields' => [
                'key' => ['label' => 'API Key (opcional)', 'type' => 'secret'],
            ],
        ],
    ],

];
