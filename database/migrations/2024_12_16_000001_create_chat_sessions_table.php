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
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_token', 64)->unique();
            $table->json('context')->nullable()->comment('Stores conversation context for multi-turn');
            $table->enum('status', ['active', 'closed', 'archived'])->default('active');
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users')
                  ->nullOnDelete();
            
            $table->index(['session_token']);
            $table->index(['user_id', 'status']);
            $table->index(['last_activity_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_sessions');
    }
};
