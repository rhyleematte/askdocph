<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // e.g. 'doctor_application'
            $table->json('data');   // payload: application_id, applicant_name, url, etc.
            $table->timestamps();
        });

        // Pivot table: which admins have read which notification
        Schema::create('admin_notification_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_notification_id');
            $table->unsignedBigInteger('admin_id');
            $table->timestamp('read_at')->nullable();

            $table->foreign('admin_notification_id')->references('id')->on('admin_notifications')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->unique(['admin_notification_id', 'admin_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notification_reads');
        Schema::dropIfExists('admin_notifications');
    }
};
