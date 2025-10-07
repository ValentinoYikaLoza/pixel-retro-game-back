<?php

namespace Database\Seeders;

use App\Models\MonthlyMissionModel;
use App\Models\UserMonthlyMissionModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class UserMonthlyMissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserMonthlyMissionModel::truncate();

        $faker = Faker::create();

        $monthly_missions_id = MonthlyMissionModel::pluck('id')->toArray();

        UserMonthlyMissionModel::insert([
            [
                'current_value' => 0,
                'completed_at' => null,
                'user_id' => 1,
                'monthly_mission_id' => $faker->randomElement($monthly_missions_id),
                'status_id' => 1,
            ],
        ]);
    }
}
