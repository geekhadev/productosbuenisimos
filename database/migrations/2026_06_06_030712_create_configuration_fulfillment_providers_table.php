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
        Schema::create('configuration_fulfillment_providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug', 64)->unique();
            $table->string('label');
            $table->json('fields');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });

        DB::table('configuration_fulfillment_providers')->insert([
            'id' => (string) Str::uuid(),
            'slug' => 'contraentrega',
            'label' => 'CONTRAENTREGA',
            'fields' => json_encode([
                'api_url' => ['label' => 'URL API', 'type' => 'text'],
                'user' => ['label' => 'Usuario', 'type' => 'text'],
                'pass' => ['label' => 'Contraseña', 'type' => 'secret'],
            ], JSON_THROW_ON_ERROR),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuration_fulfillment_providers');
    }
};
