<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_customer_addresses', function (Blueprint $table) {
            $table->string('city_name', 120)->nullable()->after('state_name');
            $table->string('district_name', 120)->nullable()->after('city_name');
            $table->string('street_prefix', 30)->nullable()->after('district_name');
            $table->string('house_number', 30)->nullable()->after('street_prefix');
            $table->string('zip_code', 10)->nullable()->after('house_number');
            $table->text('reference')->nullable()->after('zip_code');
        });

        // Llenar country_name con 'Mexico' en registros existentes que no lo tengan
        DB::table('sales_customer_addresses')
            ->whereNull('country_name')
            ->orWhere('country_name', '')
            ->update(['country_name' => 'Mexico']);
    }

    public function down(): void
    {
        Schema::table('sales_customer_addresses', function (Blueprint $table) {
            $table->dropColumn([
                'city_name',
                'district_name',
                'street_prefix',
                'house_number',
                'zip_code',
                'reference',
            ]);
        });
    }
};
