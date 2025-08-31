<?php

namespace Database\Seeders;

use App\Models\StatusModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['id' => StatusModel::PENDING, 'name' => 'Pending'],
            ['id' => StatusModel::IN_PROGRESS, 'name' => 'In Progress'],
            ['id' => StatusModel::COMPLETED, 'name' => 'Completed'],
            ['id' => StatusModel::FAILED, 'name' => 'Failed'],
        ];

        foreach ($rows as $row) {
            StatusModel::updateOrCreate(
                ['id' => $row['id']],
                $row
            );
        }
    }
}
