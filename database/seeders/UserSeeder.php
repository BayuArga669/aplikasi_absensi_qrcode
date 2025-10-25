<?php

// database/seeders/UserSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@company.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'employee_id' => 'EMP001',
            'position' => 'System Administrator',
            'department' => 'IT',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        // Superior 1
        $superior1 = User::create([
            'name' => 'John Superior',
            'email' => 'john.superior@company.com',
            'password' => Hash::make('password'),
            'role' => 'superior',
            'employee_id' => 'EMP002',
            'position' => 'Team Leader',
            'department' => 'Sales',
            'phone' => '081234567891',
            'is_active' => true,
        ]);

        // Superior 2
        $superior2 = User::create([
            'name' => 'Jane Manager',
            'email' => 'jane.manager@company.com',
            'password' => Hash::make('password'),
            'role' => 'superior',
            'employee_id' => 'EMP003',
            'position' => 'Manager',
            'department' => 'Marketing',
            'phone' => '081234567892',
            'is_active' => true,
        ]);

        // Employees under Superior 1
        User::create([
            'name' => 'Alice Employee',
            'email' => 'alice@company.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'employee_id' => 'EMP004',
            'position' => 'Sales Executive',
            'department' => 'Sales',
            'superior_id' => $superior1->id,
            'phone' => '081234567893',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Bob Employee',
            'email' => 'bob@company.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'employee_id' => 'EMP005',
            'position' => 'Sales Executive',
            'department' => 'Sales',
            'superior_id' => $superior1->id,
            'phone' => '081234567894',
            'is_active' => true,
        ]);

        // Employees under Superior 2
        User::create([
            'name' => 'Charlie Employee',
            'email' => 'charlie@company.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'employee_id' => 'EMP006',
            'position' => 'Marketing Specialist',
            'department' => 'Marketing',
            'superior_id' => $superior2->id,
            'phone' => '081234567895',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Diana Employee',
            'email' => 'diana@company.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'employee_id' => 'EMP007',
            'position' => 'Content Creator',
            'department' => 'Marketing',
            'superior_id' => $superior2->id,
            'phone' => '081234567896',
            'is_active' => true,
        ]);
    }
}