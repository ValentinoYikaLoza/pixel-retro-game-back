<?php

namespace Database\Seeders;

use App\Models\LiveShopModel;
use Illuminate\Database\Seeder;

class LiveShopSeeder extends Seeder
{
    public function run(): void
    {
        LiveShopModel::truncate();

        // Las vidas se compran SOLO con monedas (el dinero real solo compra
        // monedas). El precio en monedas es proporcional a lo que valían en
        // dinero, con la tarifa base de la tienda de monedas (100 ≈ $1.99):
        // $1.99→100, $4.99→250, $8.99→450, $14.99→750. type_id 2 = monedas.
        $rows = [
            ['id' => 1, 'quantity' => 5, 'price' => 100, 'type_id' => 2],
            ['id' => 2, 'quantity' => 15, 'price' => 250, 'type_id' => 2],
            ['id' => 3, 'quantity' => 30, 'price' => 450, 'type_id' => 2],
            ['id' => 4, 'quantity' => 50, 'price' => 750, 'type_id' => 2],
        ];

        foreach ($rows as $row) {
            LiveShopModel::updateOrCreate(['id' => $row['id']], $row);
        }
    }
}
