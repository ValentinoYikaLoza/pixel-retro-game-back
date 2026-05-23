<?php

namespace Database\Seeders;

use App\Models\LiveShopModel;
use Illuminate\Database\Seeder;

class LiveShopSeeder extends Seeder
{
    public function run(): void
    {
        LiveShopModel::truncate();

        // type_id: 1 = precio en USD, 2 = precio en monedas.
        $rows = [
            ['id' => 1, 'quantity' => 5, 'price' => 1.99, 'type_id' => 1],
            ['id' => 2, 'quantity' => 15, 'price' => 4.99, 'type_id' => 1],
            ['id' => 3, 'quantity' => 30, 'price' => 8.99, 'type_id' => 1],
            ['id' => 4, 'quantity' => 50, 'price' => 14.99, 'type_id' => 1],
            ['id' => 5, 'quantity' => 5, 'price' => 120, 'type_id' => 2],
            ['id' => 6, 'quantity' => 15, 'price' => 300, 'type_id' => 2],
            ['id' => 7, 'quantity' => 30, 'price' => 540, 'type_id' => 2],
            ['id' => 8, 'quantity' => 50, 'price' => 900, 'type_id' => 2],
        ];

        foreach ($rows as $row) {
            LiveShopModel::updateOrCreate(['id' => $row['id']], $row);
        }
    }
}
