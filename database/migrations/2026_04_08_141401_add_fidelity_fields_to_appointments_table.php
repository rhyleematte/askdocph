<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFidelityFieldsToAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('location')->nullable()->after('subject');
            $table->integer('reminder_minutes')->default(15)->after('end_at');
            $table->boolean('auto_send_brief')->default(false)->after('reminder_minutes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['location', 'reminder_minutes', 'auto_send_brief']);
        });
    }
}
