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
        Schema::create('stock_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')
                ->constrained('configuration_companies')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->string('sku');
            $table->decimal('width', 12, 3)->default(0);
            $table->decimal('length', 12, 3)->default(0);
            $table->decimal('height', 12, 3)->default(0);
            $table->decimal('volume', 12, 3)->default(0);
            $table->decimal('weight', 12, 3)->default(0);
            $table->unsignedInteger('minimum_stock')->default(0);
            $table->decimal('price', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index('company_id');
            $table->index('is_active');
            $table->index('deleted_at');
            $table->unique(['company_id', 'name'], 'stock_products_company_name_unique');
            $table->unique(['company_id', 'code'], 'stock_products_company_code_unique');
            $table->unique(['company_id', 'sku'], 'stock_products_company_sku_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_products');
    }
};
