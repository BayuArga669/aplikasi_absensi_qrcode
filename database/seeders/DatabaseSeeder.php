<?php

// database/seeders/DatabaseSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WorkScheduleSeeder::class,
            OfficeLocationSeeder::class,
            UserSeeder::class,
        ]);
    }
}