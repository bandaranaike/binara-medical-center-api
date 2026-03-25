<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (config('app.super_admin.email') && config('app.super_admin.password')) {
            $user = User::query()->firstOrNew([
                'email' => config('app.super_admin.email'),
            ]);

            if (! $user->exists) {
                $user->uuid = (string) Str::uuid();
            }

            $user->name = 'Administrator';
            $user->password = Hash::make(config('app.super_admin.password'));
            $user->role_id = Role::where('key', UserRole::ADMIN->value)->first()?->id ?? 1;
            $user->save();
        }
    }
}
