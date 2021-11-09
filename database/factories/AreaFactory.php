<?php

namespace Database\Factories;

use App\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;

class AreaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Area::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'sloc' => $this->faker->numberBetween(1111, 9999),
            'name' => $this->faker->unique->colorName(),
            'group' => 'Area Group 1'
        ];
    }
}
