<?php

namespace Database\Seeders;

use App\Models\RewardModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RewardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['id' => RewardModel::GOLD, 'name' => 'Gold'],
            ['id' => RewardModel::SILVER, 'name' => 'Silver'],
            ['id' => RewardModel::BRONZE, 'name' => 'Bronze'],
        ];

        foreach ($rows as $row) {
            RewardModel::updateOrCreate(
                ['id' => $row['id']],
                $row
            );
        }
    }
}
