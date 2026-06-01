<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Proveedores y modelos del agente de ventas
    |--------------------------------------------------------------------------
    |
    | Catálogo de proveedores de IA y los modelos permitidos por cada uno.
    | Se usa en la configuración del agente (UI y validación) y en runtime.
    | Solo aparecen en el selector los que tengan API key en Configuración → Proveedores de IA.
    |
    */

    'providers' => [
        'openai' => [
            'label' => 'OpenAI',
            'models' => [
                [
                    'value' => 'gpt-4o-mini',
                    'label' => 'GPT-4o Mini',
                ],
            ],
        ],
        'anthropic' => [
            'label' => 'Anthropic',
            'models' => [
                [
                    'value' => 'claude-3-5-haiku-latest',
                    'label' => 'Claude 3.5 Haiku',
                ],
            ],
        ],
        'gemini' => [
            'label' => 'Google Gemini',
            'models' => [
                [
                    'value' => 'gemini-2.0-flash',
                    'label' => 'Gemini 2.0 Flash',
                ],
            ],
        ],
        'groq' => [
            'label' => 'Groq',
            'models' => [
                [
                    'value' => 'llama-3.3-70b-versatile',
                    'label' => 'Llama 3.3 70B',
                ],
            ],
        ],
        'mistral' => [
            'label' => 'Mistral',
            'models' => [
                [
                    'value' => 'mistral-small-latest',
                    'label' => 'Mistral Small',
                ],
            ],
        ],
        'deepseek' => [
            'label' => 'DeepSeek',
            'models' => [
                [
                    'value' => 'deepseek-chat',
                    'label' => 'DeepSeek Chat',
                ],
            ],
        ],
        'cohere' => [
            'label' => 'Cohere',
            'models' => [
                [
                    'value' => 'command-r-plus',
                    'label' => 'Command R+',
                ],
            ],
        ],
        'openrouter' => [
            'label' => 'OpenRouter',
            'models' => [
                [
                    'value' => 'openai/gpt-4o-mini',
                    'label' => 'OpenAI GPT-4o Mini',
                ],
            ],
        ],
        'ollama' => [
            'label' => 'Ollama',
            'models' => [
                [
                    'value' => 'llama3.2',
                    'label' => 'Llama 3.2',
                ],
            ],
        ],
    ],

];
