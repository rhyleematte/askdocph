<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DoctorRequirementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear out old ones
        DB::table('doctor_requirements')->delete();

        $requirements = [
            [
                'name' => 'Licensed ID',
                'description' => 'A clear photo of the front and back of your professional Licensed ID.',
                'is_required' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Liveliness Video',
                'description' => 'A short video to verify biometrics liveness and face matching.',
                'is_required' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Resume / Portfolio',
                'description' => 'A document detailing your professional experience.',
                'is_required' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Certificates',
                'description' => 'Any other relevant medical or professional certificates.',
                'is_required' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('doctor_requirements')->insert($requirements);
    }
}
