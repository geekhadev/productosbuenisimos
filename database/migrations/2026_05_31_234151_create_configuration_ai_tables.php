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
        Schema::create('configuration_ai_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('default_provider', 64)->nullable();
            $table->timestamps();
        });

        Schema::create('configuration_ai_provider_credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('provider', 64)->unique();
            $table->text('credentials');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuration_ai_provider_credentials');
        Schema::dropIfExists('configuration_ai_settings');
    }
};
