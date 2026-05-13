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
        Schema::table('sales_customer_addresses', function (Blueprint $table) {
            $table->dropUnique(['customer_id']);
        });

        Schema::table('sales_customer_addresses', function (Blueprint $table) {
            $table->unsignedSmallInteger('sort_order')->default(0)->after('customer_id');
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_customer_addresses', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->dropColumn('sort_order');
        });

        Schema::table('sales_customer_addresses', function (Blueprint $table) {
            $table->unique('customer_id');
        });
    }
};
