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
        Schema::create('configuration_companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('document_type');
            $table->string('document_number');
            $table->string('name');
            $table->string('alias')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();

            $table->unique(['document_type', 'document_number'], 'configuration_companies_document_unique');
            $table->unique(['owner_id', 'name'], 'configuration_companies_owner_name_unique');
            $table->unique(['owner_id', 'alias'], 'configuration_companies_owner_alias_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuration_companies');
    }
};
