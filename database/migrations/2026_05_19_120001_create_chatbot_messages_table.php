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
        Schema::create('chatbot_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('chatbot_conversation_id')
                ->constrained('chatbot_conversations')
                ->cascadeOnDelete();
            $table->string('role');
            $table->string('source');
            $table->text('content');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['chatbot_conversation_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_messages');
    }
};
