<?php

namespace Database\Seeders;

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
        DB::table('users')->insert([
            'name' => 'Eranda',
            'email' => 'eranda@email.com',
            'password' => Hash::make('9,$wCD:Kf,3YwEu'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
