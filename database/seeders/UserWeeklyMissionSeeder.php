<?php

namespace Database\Seeders;

use App\Models\UserWeeklyMissionModel;
use App\Models\WeeklyMissionModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class UserWeeklyMissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserWeeklyMissionModel::truncate();

        $faker = Faker::create();

        $weekly_missions_id = WeeklyMissionModel::pluck('id')->toArray();

        UserWeeklyMissionModel::insert([
            [
                'current_value' => 0,
                'completed_at' => null,
                'user_id' => 1,
                'weekly_mission_id' => $faker->randomElement($weekly_missions_id),
                'status_id' => 1,
            ],
        ]);
    }
}
