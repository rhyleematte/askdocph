<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->timestamp('joined_at')->nullable();

            // keep loose (no FK) to avoid circular FK issues
            $table->unsignedBigInteger('last_read_message_id')->nullable();

            $table->boolean('muted')->default(false);
            $table->boolean('archived')->default(false);

            $table->timestamps();

            $table->unique(['conversation_id', 'user_id'], 'uq_conv_user');
            $table->index(['user_id', 'conversation_id'], 'idx_part_user_conv');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_participants');
    }
};
