<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            // Adding an index to read_at dramatically increases performance
            // for unread count queries in production.
            $table->index(['read_at', 'sender_user_id', 'conversation_id'], 'idx_msgs_speed_boost');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('idx_msgs_speed_boost');
        });
    }
};
