<?php

namespace Database\Seeders;

use App\Models\TrustedSite;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TrustedSiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $data = [
            ['domain' => 'binara.live', 'api_key' => Str::random(40)],
            ['domain' => 'app.binara.live', 'api_key' => Str::random(40)],
            ['domain' => 'localhost:3000', 'api_key' => Str::random(40)],
            ['domain' => 'localhost:3001', 'api_key' => Str::random(40)],
        ];
        TrustedSite::upsert($data, ['domain']);
    }
}
