<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('configuration_company_integration_settings')
            ->where('key', 'like', 'configuration_integrations_sii%')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Los registros eliminados no se restauran de forma determinista.
    }
};
