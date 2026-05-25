<?php

namespace Database\Seeders;

use App\Models\MissionTypeModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MissionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['id' => MissionTypeModel::POINTS, 'name' => 'Points'],
            ['id' => MissionTypeModel::MATCHES, 'name' => 'Matches'],
            ['id' => MissionTypeModel::SINGLE_GAME_SCORE, 'name' => 'Single Game Score'],
        ];

        foreach ($rows as $row) {
            MissionTypeModel::updateOrCreate(
                ['id' => $row['id']],
                $row
            );
        }
    }
}
