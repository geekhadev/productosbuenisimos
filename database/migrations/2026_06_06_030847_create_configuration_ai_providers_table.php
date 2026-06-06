<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('configuration_ai_providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug', 64)->unique();
            $table->string('label');
            $table->json('fields');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });

        $now = now();
        $providers = [
            ['slug' => 'openai', 'label' => 'OpenAI'],
            ['slug' => 'anthropic', 'label' => 'Anthropic'],
            ['slug' => 'gemini', 'label' => 'Google Gemini'],
            ['slug' => 'groq', 'label' => 'Groq'],
            ['slug' => 'mistral', 'label' => 'Mistral'],
            ['slug' => 'deepseek', 'label' => 'DeepSeek'],
            ['slug' => 'cohere', 'label' => 'Cohere'],
            ['slug' => 'openrouter', 'label' => 'OpenRouter'],
            ['slug' => 'ollama', 'label' => 'Ollama', 'key_label' => 'API Key (opcional)'],
        ];

        foreach ($providers as $provider) {
            DB::table('configuration_ai_providers')->insert([
                'id' => (string) Str::uuid(),
                'slug' => $provider['slug'],
                'label' => $provider['label'],
                'fields' => json_encode([
                    'key' => [
                        'label' => $provider['key_label'] ?? 'API Key',
                        'type' => 'secret',
                    ],
                ], JSON_THROW_ON_ERROR),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuration_ai_providers');
    }
};
