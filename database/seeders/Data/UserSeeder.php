<?php

namespace Database\Seeders\Data;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{Role, Division, User};
use Faker\Generator;

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
        // renev
        User::create([
            'role' => Role::administrator(),
            'division' => Division::renev(),
            'name' => 'Administrator Renev',
            'phone' => $this->faker->phoneNumber(),
            'nip' => '000000',
            'password' => Hash::make('000000'),
        ]);
        User::create([
            'role' => Role::operator(),
            'division' => Division::renev(),
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'nip' => '111111',
            'password' => Hash::make('111111'),
        ]);

        // construction
        User::create([
            'role' => Role::administrator(),
            'division' => Division::construction(),
            'name' => 'Administrator Konstruksi',
            'phone' => $this->faker->phoneNumber(),
            'nip' => '000001',
            'password' => Hash::make('000001'),
        ]);
        User::create([
            'role' => Role::operator(),
            'division' => Division::construction(),
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'nip' => '111112',
            'password' => Hash::make('111112'),
        ]);

        // kku
        User::create([
            'role' => Role::administrator(),
            'division' => Division::kku(),
            'name' => 'Administrator KKU',
            'phone' => $this->faker->phoneNumber(),
            'nip' => '000002',
            'password' => Hash::make('000002'),
        ]);
        User::create([
            'role' => Role::operator(),
            'division' => Division::kku(),
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'nip' => '111113',
            'password' => Hash::make('111113'),
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
            'division' => Division::renev(),
            'name' => 'Administrator Renev',
            'phone' => $this->faker->phoneNumber(),
            'nip' => '000000',
            'password' => Hash::make('000000'),
        ]);
        User::create([
            'role' => Role::administrator(),
            'division' => Division::construction(),
            'name' => 'Administrator Konstruksi',
            'phone' => $this->faker->phoneNumber(),
            'nip' => '000001',
            'password' => Hash::make('000001'),
        ]);
        User::create([
            'role' => Role::administrator(),
            'division' => Division::kku(),
            'name' => 'Administrator KKU',
            'phone' => $this->faker->phoneNumber(),
            'nip' => '000002',
            'password' => Hash::make('000002'),
        ]);
    }
}
