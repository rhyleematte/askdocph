<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApplyingToDoctorStatusInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN doctor_status ENUM('none', 'applying', 'pending', 'approved', 'rejected') DEFAULT 'none'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN doctor_status ENUM('none', 'pending', 'approved', 'rejected') DEFAULT 'none'");
    }
}
