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
        Schema::create('shared_countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('name_code', 32);
            $table->string('phone_code', 32);
            $table->string('currency_name', 255);
            $table->string('currency_symbol', 32);
            $table->timestamps();

            $table->unique('name');
            $table->unique('name_code');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shared_countries');
    }
};
