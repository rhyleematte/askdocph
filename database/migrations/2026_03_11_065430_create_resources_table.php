<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The Doctor
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type'); // Article, Audio, Workbook, Media, Video
            $table->longText('content')->nullable(); // Article content or file path/link
            $table->string('thumbnail')->nullable();
            $table->string('duration_meta')->nullable(); // e.g. "10 min read"
            $table->string('hashtags')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resources');
    }
}
