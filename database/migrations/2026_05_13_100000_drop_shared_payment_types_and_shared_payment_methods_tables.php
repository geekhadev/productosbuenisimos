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
        Schema::dropIfExists('shared_payment_methods');
        Schema::dropIfExists('shared_payment_types');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('shared_payment_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->timestamps();

            $table->index('created_at');
        });

        Schema::create('shared_payment_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('code', 50)->unique();
            $table->timestamps();

            $table->index('created_at');
        });
    }
};
