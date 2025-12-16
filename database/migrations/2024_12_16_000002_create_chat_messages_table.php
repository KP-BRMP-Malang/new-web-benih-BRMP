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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('chat_session_id');
            $table->enum('role', ['user', 'assistant', 'system']);
            $table->text('content');
            $table->json('metadata')->nullable()->comment('Stores intent, filters, sources, etc.');
            $table->integer('token_count')->nullable();
            $table->timestamps();
            
            $table->foreign('chat_session_id')
                  ->references('id')
                  ->on('chat_sessions')
                  ->onDelete('cascade');
            
            $table->index(['chat_session_id', 'created_at']);
            $table->index(['role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
