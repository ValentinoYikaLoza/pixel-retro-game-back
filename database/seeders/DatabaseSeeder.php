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
        $this->call(GameSeeder::class);
        $this->call(GameLevelSeeder::class);
        $this->call(TetrisLevelSeeder::class);
        $this->call(InvadersLevelSeeder::class);
        $this->call(RewardSeeder::class);
        $this->call(StatusSeeder::class);
        $this->call(MissionTypeSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(DailyMissionSeeder::class);
        $this->call(WeeklyMissionSeeder::class);
        $this->call(MonthlyMissionSeeder::class);
        $this->call(UserDailyMissionSeeder::class);
        $this->call(UserWeeklyMissionSeeder::class);
        $this->call(UserMonthlyMissionSeeder::class);
        $this->call(AdvertisementSeeder::class);
        $this->call(CoinShopSeeder::class);
        $this->call(LiveShopSeeder::class);
        $this->call(StreakMonthlyGoalSeeder::class);
    }
}
