<?php

namespace Database\Seeders\Data;

use Illuminate\Database\Seeder;
use App\Models\Holder;

class HolderSeeder extends Seeder
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

        Holder::factory()
            ->count(5)
            ->creator(5)
            ->create();
    }
}
