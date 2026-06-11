<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_product_similar_products', function (Blueprint $table): void {
            $table->uuid('product_id');
            $table->uuid('similar_product_id');

            $table->primary(['product_id', 'similar_product_id']);

            $table->foreign('product_id')
                ->references('id')
                ->on('stock_products')
                ->cascadeOnDelete();

            $table->foreign('similar_product_id')
                ->references('id')
                ->on('stock_products')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_product_similar_products');
    }
};
