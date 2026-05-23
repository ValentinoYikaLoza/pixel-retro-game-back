<?php

namespace Database\Seeders;

use App\Models\AdvertisementModel;
use Illuminate\Database\Seeder;

class AdvertisementSeeder extends Seeder
{
    public function run(): void
    {
        AdvertisementModel::truncate();

        $rows = [
            ['id' => 1, 'reward' => 10, 'reward_type' => 'coin'],
            ['id' => 2, 'reward' => 5, 'reward_type' => 'life'],
        ];

        foreach ($rows as $row) {
            AdvertisementModel::updateOrCreate(['id' => $row['id']], $row);
        }
    }
}
