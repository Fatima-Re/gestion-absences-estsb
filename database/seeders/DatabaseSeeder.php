<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Demo data is additive (does not truncate or remove existing rows).
     * Run again anytime: php artisan db:seed --class=DemoDataSeeder
     */
    public function run(): void
    {
        $this->call(DemoDataSeeder::class);
    }
}
