<?php

namespace Database\Seeders;

use App\Models\CoinShopModel;
use Illuminate\Database\Seeder;

class CoinShopSeeder extends Seeder
{
    public function run(): void
    {
        CoinShopModel::truncate();

        $rows = [
            ['id' => 1, 'quantity' => 100, 'price' => 1.99],
            ['id' => 2, 'quantity' => 300, 'price' => 4.99],
            ['id' => 3, 'quantity' => 300, 'price' => 9.99],
            ['id' => 4, 'quantity' => 1400, 'price' => 19.99],
        ];

        foreach ($rows as $row) {
            CoinShopModel::updateOrCreate(['id' => $row['id']], $row);
        }
    }
}
