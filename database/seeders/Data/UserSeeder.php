<?php

namespace Database\Seeders\Data;

use App\Models\Role;
use App\Models\User;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * The faker instance
     *
     * @var Generator
     */
    protected mixed $faker;

    /**
     * Create a new seeder instance
     *
     * @return void
     */
    public function __construct()
    {
        try {
            $this->faker = Container::getInstance()->make(Generator::class);
        } catch (BindingResolutionException $e) {
            report($e);
        }
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->production();
        } else {
            $this->staging();
        }
    }

    protected function production(): void
    {
        User::create([
            'role' => Role::administrator(),
            'name' => 'Administrator',
            'nip' => '251781',
            'password' => Hash::make('251781')
        ]);
    }

    protected function staging(): void
    {
        User::create([
            'role' => Role::administrator(),
            'name' => 'Administrator',
            'nip' => '241178',
            'password' => Hash::make('241178')
        ]);
        User::create([
            'role' => Role::operator(),
            'name' => $this->faker->name(),
            'nip' => '752114',
            'password' => Hash::make('752114')
        ]);
        User::factory(2)->create();
    }
}
