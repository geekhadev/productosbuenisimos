<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')
                ->constrained('sales_orders')
                ->cascadeOnDelete();
            $table->foreignUuid('product_id')
                ->constrained('stock_products')
                ->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();

            $table->index('order_id');
            $table->index('product_id');
            $table->unique(['order_id', 'product_id'], 'sales_order_items_order_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
    }
};
