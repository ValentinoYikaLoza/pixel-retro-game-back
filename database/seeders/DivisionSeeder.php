<?php

namespace Database\Seeders;

use App\Models\DivisionModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DivisionModel::truncate();
        DivisionModel::insert([
            [
                'name' => 'División Bronce',
            ],
            [
                'name' => 'División Plata',
            ],
            [
                'name' => 'División Oro',
            ],
            [
                'name' => 'División Zafiro',
            ],
            [
                'name' => 'División Rubí',
            ],
            [
                'name' => 'División Esmeralda',
            ],
            [
                'name' => 'División Amatista',
            ],
            [
                'name' => 'División Perla',
            ],
            [
                'name' => 'División Obsidiana',
            ],
            [
                'name' => 'División Diamante',
            ],
        ]);
    }
}
