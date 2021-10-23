<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Laravel\Jetstream\Features;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $nip = $this->faker->unique()->numberBetween(100000, 999999);
        return [
            'role' => Role::operator(),
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'nip' => $nip,
            'password' => Hash::make($nip)
        ];
    }

    /**
     * Indicate that the user should have a personal team.
     *
     * @return $this
     */
    public function withPersonalTeam(): static
    {
        if (!Features::hasTeamFeatures()) {
            return $this->state([]);
        }
        return $this->has(
            Team::factory()->state(function (array $attributes, User $user) {
                return ['name' => $user->name . '\'s Team', 'user_id' => $user->id, 'personal_team' => true];
            }),
            'ownedTeams'
        );
    }
}
