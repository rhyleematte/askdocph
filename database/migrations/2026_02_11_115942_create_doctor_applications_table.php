<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('doctor_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->foreignId('reviewed_by_admin_id')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->text('admin_notes')->nullable();

            $table->timestamps();

            // One application per user (change if you want multiple)
            $table->unique('user_id', 'uq_docapp_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_applications');
    }
};
