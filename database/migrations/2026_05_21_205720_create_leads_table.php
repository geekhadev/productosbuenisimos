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
        Schema::create('sales_leads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')
                ->constrained('configuration_companies')
                ->cascadeOnDelete();
            $table->string('phone', 40);
            $table->string('source');
            $table->string('status')->default('nuevo');
            $table->foreignUuid('customer_id')
                ->nullable()
                ->constrained('sales_customers')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index(['company_id', 'phone']);
            $table->index(['company_id', 'status']);
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_leads');
    }
};
