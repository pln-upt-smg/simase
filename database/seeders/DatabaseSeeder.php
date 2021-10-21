<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
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
        Schema::enableForeignKeyConstraints();
    }

    protected function seeds(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
    }
}
