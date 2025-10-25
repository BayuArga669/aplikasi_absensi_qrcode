<?php

// database/seeders/OfficeLocationSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OfficeLocation;

class OfficeLocationSeeder extends Seeder
{
    public function run(): void
    {
        OfficeLocation::create([
            'name' => 'Head Office Jakarta',
            'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
            'latitude' => -6.208763,
            'longitude' => 106.845599,
            'radius' => 50,
            'is_active' => true,
        ]);

        OfficeLocation::create([
            'name' => 'Branch Office Surabaya',
            'address' => 'Jl. Tunjungan No. 45, Surabaya',
            'latitude' => -7.257472,
            'longitude' => 112.752088,
            'radius' => 50,
            'is_active' => true,
        ]);
    }
}