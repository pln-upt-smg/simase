<?php

namespace Database\Seeders\Data;

use App\Models\SubArea;
use Illuminate\Database\Seeder;

class SubAreaSeeder extends Seeder
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
        SubArea::factory(5)->create();
    }
}
