<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('sender_user_id')->constrained('users')->cascadeOnDelete();

            $table->enum('message_type', ['text', 'image', 'video', 'file'])->default('text');
            $table->text('body')->nullable();

            $table->timestamps();

            $table->index(['conversation_id', 'created_at'], 'idx_msg_conv_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
