<?php

namespace Database\Seeders\Data;

use Illuminate\Database\Seeder;
use App\Models\AreaType;

class AreaSeeder extends Seeder
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
        AreaType::create(['created_by' => 1, 'name' => 'Tower SUTET']);
        AreaType::create(['created_by' => 1, 'name' => 'Tower SUTT']);
    }
}
