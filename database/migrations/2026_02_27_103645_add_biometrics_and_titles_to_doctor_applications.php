<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBiometricsAndTitlesToDoctorApplications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('doctor_applications', function (Blueprint $table) {
            $table->string('professional_titles')->nullable()->after('user_id');
            $table->boolean('biometric_consent')->default(false)->after('professional_titles');
            $table->boolean('liveness_verified')->default(false)->after('biometric_consent');
            $table->decimal('face_match_score', 5, 2)->nullable()->after('liveness_verified');
            $table->timestamp('biometric_verified_at')->nullable()->after('face_match_score');
            $table->string('biometric_reference_hash')->nullable()->after('biometric_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('doctor_applications', function (Blueprint $table) {
            $table->dropColumn([
                'professional_titles',
                'biometric_consent',
                'liveness_verified',
                'face_match_score',
                'biometric_verified_at',
                'biometric_reference_hash'
            ]);
        });
    }
}
