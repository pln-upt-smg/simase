<?php

namespace Database\Factories;

use App\Models\SubArea;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubAreaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SubArea::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'area_id' => $this->faker->numberBetween(1, 2),
            'name' => $this->faker->unique->colorName()
        ];
    }
}
