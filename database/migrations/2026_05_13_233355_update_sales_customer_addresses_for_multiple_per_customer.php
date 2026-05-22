<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        $keepIds = DB::table('sales_customer_addresses')
            ->orderBy('customer_id')
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get(['id', 'customer_id'])
            ->unique('customer_id')
            ->pluck('id')
            ->all();

        if ($keepIds !== []) {
            DB::table('sales_customer_addresses')
                ->whereNotIn('id', $keepIds)
                ->delete();
        }

        Schema::table('sales_customer_addresses', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->dropColumn('sort_order');
        });

        Schema::table('sales_customer_addresses', function (Blueprint $table) {
            $table->unique('customer_id');
        });
    }
};
