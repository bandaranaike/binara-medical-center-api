<?php

namespace Database\Seeders;

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
                'key' => Role::ROLE_ADMIN,
                'description' => 'Administrator with full access.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Patient',
                'key' => Role::ROLE_PATIENT,
                'description' => 'User role for patients.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Pharmacy',
                'key' => Role::ROLE_PHARMACY,
                'description' => 'Role for pharmacy users.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Doctor',
                'key' => Role::ROLE_DOCTOR,
                'description' => 'Role for doctors.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Nurse',
                'key' => Role::ROLE_NURSE,
                'description' => 'Role for nurses.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Reception',
                'key' => Role::ROLE_RECEPTION,
                'description' => 'Role for reception staff.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Pharmacy admin',
                'key' => Role::ROLE_PHARMACY_ADMIN,
                'description' => 'Role for pharmacy admin.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        Role::upsert($roles, ['key'], ['name', 'description', 'updated_at']);
    }
}
