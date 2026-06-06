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
        Schema::create('configuration_whatsapp_providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug', 64)->unique();
            $table->string('label');
            $table->json('fields');
            $table->string('webhook_route')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });

        $now = now();

        DB::table('configuration_whatsapp_providers')->insert([
            'id' => (string) Str::uuid(),
            'slug' => 'twilio',
            'label' => 'Twilio',
            'fields' => json_encode([
                'account_sid' => ['label' => 'Account SID', 'type' => 'text'],
                'auth_token' => ['label' => 'Auth Token', 'type' => 'secret'],
                'from_number' => [
                    'label' => 'Número de origen',
                    'type' => 'text',
                    'placeholder' => 'whatsapp:+14155238886',
                ],
            ], JSON_THROW_ON_ERROR),
            'webhook_route' => 'webhook.whatsapp.twilio',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('configuration_whatsapp_providers')->insert([
            'id' => (string) Str::uuid(),
            'slug' => 'meta',
            'label' => 'Meta',
            'fields' => json_encode([
                'access_token' => ['label' => 'Access Token', 'type' => 'secret'],
                'phone_number_id' => ['label' => 'Phone Number ID', 'type' => 'text'],
                'verify_token' => ['label' => 'Verify Token', 'type' => 'secret'],
                'app_secret' => ['label' => 'App Secret', 'type' => 'secret'],
            ], JSON_THROW_ON_ERROR),
            'webhook_route' => 'webhook.whatsapp.meta',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuration_whatsapp_providers');
    }
};
