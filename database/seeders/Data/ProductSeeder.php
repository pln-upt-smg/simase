<?php

namespace Database\Seeders\Data;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
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
        Product::factory(5)->create();
    }
}
