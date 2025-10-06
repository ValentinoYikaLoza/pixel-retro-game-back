<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CountrySeeder::class);
        $this->call(DivisionSeeder::class);
        $this->call(FrequencySeeder::class);
        $this->call(GameSeeder::class);
        $this->call(RewardSeeder::class);
        $this->call(StatusSeeder::class);
        $this->call(MissionTypeSeeder::class);
        $this->call(UserSeeder::class);
        // $this->call(MissionSeeder::class);
    }
}
