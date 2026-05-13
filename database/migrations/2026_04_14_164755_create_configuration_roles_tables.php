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
        Schema::create('configuration_roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->boolean('is_public')->default(false);
            $table->foreignUuid('company_id')
                ->nullable()
                ->constrained('configuration_companies')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'created_at']);
            $table->index('created_at');
            $table->unique(['company_id', 'name']);
        });

        Schema::create('configuration_role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('configuration_role_id')
                ->constrained('configuration_roles')
                ->cascadeOnDelete();
            $table->foreignUuid('permission_id')
                ->constrained('administration_permissions')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['configuration_role_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuration_role_permission');
        Schema::dropIfExists('configuration_roles');
    }
};
