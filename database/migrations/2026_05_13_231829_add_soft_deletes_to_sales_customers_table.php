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
        Schema::table('sales_customers', function (Blueprint $table) {
            $table->dropUnique('sales_customers_company_phone_unique');
        });

        Schema::table('sales_customers', function (Blueprint $table) {
            $table->softDeletes();
            $table->index(['company_id', 'phone'], 'sales_customers_company_phone_index');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_customers', function (Blueprint $table) {
            $table->dropIndex('sales_customers_company_phone_index');
            $table->dropIndex('sales_customers_deleted_at_index');
            $table->dropSoftDeletes();
        });

        Schema::table('sales_customers', function (Blueprint $table) {
            $table->unique(['company_id', 'phone'], 'sales_customers_company_phone_unique');
        });
    }
};
