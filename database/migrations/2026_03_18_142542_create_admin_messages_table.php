<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_admin_id');
            $table->unsignedBigInteger('to_admin_id');
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('from_admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->foreign('to_admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index(['from_admin_id', 'to_admin_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_messages');
    }
};
