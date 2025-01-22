<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->upsert(
            [
                'email' => 'admin@email.com',
                'name' => 'Administrator',
                'password' => Hash::make('9,$wCD:Kf,3YwEu'),
                'role_id' => Role::where('key', Role::ROLE_ADMIN)->first()?->id ?? 1, // Make sure RolesSeeder has already been run
                'created_at' => now(),
                'updated_at' => now(),
            ],
            ['email'],
            ['name', 'password', 'role_id', 'created_at', 'updated_at']
        );
    }
}
