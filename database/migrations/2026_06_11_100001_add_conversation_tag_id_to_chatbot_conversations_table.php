<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbot_conversations', function (Blueprint $table) {
            $table->foreignUuid('conversation_tag_id')
                ->nullable()
                ->after('agent_paused')
                ->constrained('sales_conversation_tags')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('chatbot_conversations', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Sales\ConversationTag::class);
            $table->dropColumn('conversation_tag_id');
        });
    }
};
