<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')
                ->constrained('configuration_companies')
                ->cascadeOnDelete();
            $table->string('name');
            $table->foreignUuid('customer_id')
                ->constrained('sales_customers')
                ->cascadeOnDelete();
            $table->foreignUuid('address_id')
                ->constrained('sales_customer_addresses')
                ->cascadeOnDelete();
            $table->decimal('total_amount', 12, 2);
            $table->timestamps();

            $table->index('company_id');
            $table->index('customer_id');
            $table->index('address_id');
            $table->unique(['company_id', 'name'], 'sales_orders_company_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
