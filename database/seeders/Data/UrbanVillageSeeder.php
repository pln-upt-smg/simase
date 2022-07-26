<?php

namespace Database\Seeders\Data;

use Illuminate\Database\Seeder;
use App\Models\UrbanVillage;

class UrbanVillageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        if (app()->isProduction() && !config('app.env_staging')) {
            return;
        }

        UrbanVillage::factory()
            ->count(5)
            ->creator(5)
            ->create();
    }
}
