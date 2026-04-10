<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('doctor_application_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('doctor_application_id')
                ->constrained('doctor_applications')
                ->cascadeOnDelete();

            $table->foreignId('doctor_requirement_id')
                ->constrained('doctor_requirements')
                ->cascadeOnDelete();

            $table->enum('document_type', ['file', 'text'])->default('file');
            $table->string('file_path')->nullable();
            $table->text('text_value')->nullable();

            $table->enum('status', ['submitted', 'accepted', 'rejected'])->default('submitted');

            $table->timestamps();

            $table->unique(
            ['doctor_application_id', 'doctor_requirement_id'],
                'uq_docapp_req'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_application_documents');
    }
};
