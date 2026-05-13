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
        Schema::create('shared_states', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('country_id')
                ->constrained('shared_countries')
                ->restrictOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->index('country_id');
            $table->unique(['country_id', 'name']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shared_states');
    }
};
