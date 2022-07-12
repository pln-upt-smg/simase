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
        if (app()->isLocal() || config('app.env_staging')) {
            $this->staging();
        } else {
            $this->production();
        }
    }

    /**
     * Run the database seeds in staging environment.
     *
     * @return void
     */
    protected function staging(): void
    {
        User::create([
            'role' => Role::administrator(),
            'name' => 'Administrator',
            'phone' => $this->faker->phoneNumber(),
            'nip' => '000000',
            'password' => Hash::make('000000'),
        ]);
        User::create([
            'role' => Role::operator(),
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'nip' => '111111',
            'password' => Hash::make('111111'),
        ]);
        User::factory(19)->create();
    }

    /**
     * Run the database seeds in production environment.
     *
     * @return void
     */
    protected function production(): void
    {
        User::create([
            'role' => Role::administrator(),
            'name' => 'Administrator',
            'phone' => $this->faker->phoneNumber(),
            'nip' => '000000',
            'password' => Hash::make('000000'),
        ]);
    }
}
