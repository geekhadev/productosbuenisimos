<?php

use App\Models\Configuration\AiProviderCredential;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('configuration_ai_provider_credentials', function (Blueprint $table) {
            $table->timestamp('key_updated_at')->nullable()->after('credentials');
            $table->string('key_last_chars', 16)->nullable()->after('key_updated_at');
        });

        foreach (AiProviderCredential::query()->cursor() as $credential) {
            /** @var array<string, mixed> $credentials */
            $credentials = $credential->credentials ?? [];
            $key = $credentials['key'] ?? null;

            if (! is_string($key) || $key === '') {
                continue;
            }

            $credential->update([
                'key_last_chars' => AiProviderCredential::hintFromKey($key),
                'key_updated_at' => $credential->updated_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuration_ai_provider_credentials', function (Blueprint $table) {
            $table->dropColumn(['key_updated_at', 'key_last_chars']);
        });
    }
};
