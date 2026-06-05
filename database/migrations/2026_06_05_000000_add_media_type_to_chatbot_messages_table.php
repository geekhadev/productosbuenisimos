<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbot_messages', function (Blueprint $table): void {
            $table->string('media_type')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('chatbot_messages', function (Blueprint $table): void {
            $table->dropColumn('media_type');
        });
    }
};
