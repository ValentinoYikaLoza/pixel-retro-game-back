<?php

namespace Database\Seeders;

use App\Models\FrequencyModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FrequencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            [
                'id' => FrequencyModel::MONTHLY,
                'name' => 'Monthly frequency',
            ],
            [
                'id' => FrequencyModel::WEEKLY,
                'name' => 'Weekly frequency',
            ],
            [
                'id' => FrequencyModel::DAILY,
                'name' => 'Daily frequency',
            ],
        ];

        foreach ($rows as $row) {
            FrequencyModel::updateOrCreate(
                ['id' => $row['id']],
                $row
            );
        }
    }
}
