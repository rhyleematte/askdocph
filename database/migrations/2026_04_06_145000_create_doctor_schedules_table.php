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
        Schema::create('doctor_schedules', function (Blueprint $row) {
            $row->id();
            $row->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $row->unsignedTinyInteger('day_of_week'); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
            $row->time('start_time')->default('08:00:00');
            $row->time('end_time')->default('17:00:00');
            $row->boolean('is_active')->default(false);
            $row->timestamps();

            $row->unique(['doctor_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_schedules');
    }
};
