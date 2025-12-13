<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        if (config('app.super_admin.email') && config('app.super_admin.password')) {
            DB::table('users')->upsert(
                [
                    'email' => config('app.super_admin.email'),
                    'uuid' => Str::uuid(),
                    'name' => 'Administrator',
                    'password' => Hash::make(config('app.super_admin.password')),
                    'role_id' => Role::where('key', UserRole::ADMIN->value)->first()?->id ?? 1, // Make sure RolesSeeder has already been run
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                ['email'],
                ['name', 'password', 'role_id', 'created_at', 'updated_at']
            );
        }
    }
}
