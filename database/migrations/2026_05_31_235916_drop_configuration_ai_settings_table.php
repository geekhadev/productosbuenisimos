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
        Schema::dropIfExists('configuration_ai_settings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('configuration_ai_settings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('default_provider', 64)->nullable();
            $table->timestamps();
        });
    }
};
