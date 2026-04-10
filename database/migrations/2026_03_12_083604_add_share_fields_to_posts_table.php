<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShareFieldsToPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('shared_post_id')->nullable()->constrained('posts')->cascadeOnDelete();
        });
        
        // Alter enum to string (VARCHAR) natively to support resource_share, post_share, etc.
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE posts MODIFY COLUMN post_type VARCHAR(255) DEFAULT 'text'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['shared_post_id']);
            $table->dropColumn('shared_post_id');
        });
        
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE posts MODIFY COLUMN post_type ENUM('text', 'media', 'mixed') DEFAULT 'text'");

    }
}
