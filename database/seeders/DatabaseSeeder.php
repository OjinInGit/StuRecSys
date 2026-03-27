<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Order matters — run reference data first
        // before anything that depends on them
        $this->call([
            AdminSeeder::class,
        ]);
    }
}
