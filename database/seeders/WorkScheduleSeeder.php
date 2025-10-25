<?php

// database/seeders/WorkScheduleSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkSchedule;

class WorkScheduleSeeder extends Seeder
{
    public function run(): void
    {
        WorkSchedule::create([
            'name' => 'Regular Shift',
            'check_in_time' => '08:00:00',
            'check_out_time' => '17:00:00',
            'late_tolerance' => 15,
            'is_default' => true,
        ]);

        WorkSchedule::create([
            'name' => 'Early Shift',
            'check_in_time' => '06:00:00',
            'check_out_time' => '15:00:00',
            'late_tolerance' => 10,
            'is_default' => false,
        ]);

        WorkSchedule::create([
            'name' => 'Night Shift',
            'check_in_time' => '22:00:00',
            'check_out_time' => '07:00:00',
            'late_tolerance' => 15,
            'is_default' => false,
        ]);
    }
}
