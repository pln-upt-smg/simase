<?php

namespace Database\Seeders\Data;

use Illuminate\Database\Seeder;
use App\Models\Division;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Division::updateOrCreate(['name' => 'Renev']);
        Division::updateOrCreate(['name' => 'Construction']);
        Division::updateOrCreate(['name' => 'KKU']);
    }
}
