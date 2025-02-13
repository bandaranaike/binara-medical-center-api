<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'key' => UserRole::ADMIN,
                'description' => 'Administrator with full access.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Patient',
                'key' => UserRole::PATIENT,
                'description' => 'User role for patients.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Pharmacy',
                'key' => UserRole::PHARMACY,
                'description' => 'Role for pharmacy users.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Doctor',
                'key' => UserRole::DOCTOR,
                'description' => 'Role for doctors.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Nurse',
                'key' => UserRole::NURSE,
                'description' => 'Role for nurses.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Reception',
                'key' => UserRole::RECEPTION,
                'description' => 'Role for reception staff.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Pharmacy admin',
                'key' => UserRole::PHARMACY_ADMIN,
                'description' => 'Role for pharmacy admin.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        Role::upsert($roles, ['key'], ['name', 'description', 'updated_at']);
    }
}
