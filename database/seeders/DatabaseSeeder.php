<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\MenuSeeder;
use Database\Seeders\DepartemenSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call([
            AdminUserSeeder::class,
            MenuSeeder::class,
            DepartemenSeeder::class,
        ]);
    }
}
