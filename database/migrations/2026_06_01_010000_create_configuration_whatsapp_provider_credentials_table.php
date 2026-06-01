<?php

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
        Schema::create('configuration_whatsapp_provider_credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('provider', 64)->unique();
            $table->text('credentials');
            $table->timestamp('credentials_updated_at')->nullable();
            $table->json('secret_field_hints')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuration_whatsapp_provider_credentials');
    }
};
