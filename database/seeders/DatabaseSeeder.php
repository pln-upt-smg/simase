<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Material;
use App\Models\Period;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\Data\AreaSeeder;
use Database\Seeders\Data\MaterialSeeder;
use Database\Seeders\Data\PeriodSeeder;
use Database\Seeders\Data\RoleSeeder;
use Database\Seeders\Data\UserSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        if (config('app.truncate_on_db_seed')) {
            $this->reset();
        }
        $this->seeds();
    }

    protected function reset(): void
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Role::truncate();
        Period::truncate();
        Area::truncate();
        Material::truncate();
        Schema::enableForeignKeyConstraints();
    }

    protected function seeds(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(PeriodSeeder::class);
        $this->call(AreaSeeder::class);
        $this->call(MaterialSeeder::class);
    }
}
