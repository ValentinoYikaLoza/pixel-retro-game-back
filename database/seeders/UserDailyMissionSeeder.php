<?php

namespace Database\Seeders;

use App\Models\DailyMissionModel;
use App\Models\UserDailyMissionModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class UserDailyMissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserDailyMissionModel::truncate();
        $faker = Faker::create();

        $daily_missions_id = DailyMissionModel::pluck('id')->toArray();

        UserDailyMissionModel::insert([
            [
                'current_value' => 0,
                'completed_at' => null,
                'user_id' => 1,
                'daily_mission_id' => $faker->randomElement($daily_missions_id),
                'status_id' => 1,
            ],
            [
                'current_value' => 0,
                'completed_at' => null,
                'user_id' => 1,
                'daily_mission_id' => $faker->randomElement($daily_missions_id),
                'status_id' => 1,
            ],
            [
                'current_value' => 0,
                'completed_at' => null,
                'user_id' => 1,
                'daily_mission_id' => $faker->randomElement($daily_missions_id),
                'status_id' => 1,
            ],
        ]);
    }
}
