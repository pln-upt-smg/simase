<?php

namespace Database\Seeders\Data;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Role::updateOrCreate(['name' => 'Administrator']);
        Role::updateOrCreate(['name' => 'Operator']);
    }
}
