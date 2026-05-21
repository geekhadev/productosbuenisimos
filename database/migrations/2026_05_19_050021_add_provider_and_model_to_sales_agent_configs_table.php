<?php

use App\Models\Sales\SalesAgentConfig;
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
        Schema::table('sales_agent_configs', function (Blueprint $table) {
            $table->string('provider', 64)
                ->default(SalesAgentConfig::DEFAULT_PROVIDER)
                ->after('enabled_tools');
            $table->string('model', 128)
                ->nullable()
                ->after('provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_agent_configs', function (Blueprint $table) {
            $table->dropColumn(['provider', 'model']);
        });
    }
};
