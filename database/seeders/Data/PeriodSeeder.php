<?php

namespace Database\Seeders\Data;

use App\Models\Period;
use Illuminate\Database\Seeder;

class PeriodSeeder extends Seeder
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
        for ($i = 1; $i <= 4; $i++) {
            Period::create(['name' => date('Y') . " - Quarter $i"]);
        }
    }
}
