<?php

namespace Database\Seeders;

use App\Models\CountryModel;
use App\Models\DivisionModel;
use App\Models\UserModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Auth\User;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpia la tabla antes de insertar (opcional)
        UserModel::truncate();

        $faker = Faker::create();
        $users_to_generate = 1000; // 🔹 Cambia este número según quieras
        $divisions_id = DivisionModel::pluck('id')->toArray();
        $countries_id = CountryModel::pluck('id')->toArray();

        $users = [];

        UserModel::insert([
            [
                'name' => 'Valentino',
                'score' => 10000,
                'coins' => 5000,
                'lives' => 100,
                'streak' => 30,
                'times_ranked_first' => 100,
                'division_id' => 1,
                'country_id' => 168,
            ],
        ]);

        for ($i = 0; $i < $users_to_generate; $i++) {
            $users[] = [
                'name' => $faker->name,
                'score' => $faker->numberBetween(0, 1000),
                'coins' => $faker->numberBetween(0, 500),
                'lives' => $faker->numberBetween(1, 10),
                'streak' => $faker->numberBetween(0, 30),
                'times_ranked_first' => $faker->numberBetween(0, 100),
                'division_id' => $faker->randomElement($divisions_id),
                'country_id' => $faker->randomElement($countries_id),
            ];
        }

        // Inserta todo de una sola vez
        UserModel::insert($users);
    }
}
