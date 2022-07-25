<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Area;

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
            'funcloc' => $this->faker->unique->numberBetween(1111, 9999),
            'name' => Str::title($this->faker->unique->words(1, 1)),
            'lat' => $this->faker->randomFloat(),
            'lon' => $this->faker->randomFloat(),
        ];
    }

    /**
     * Indicate the area type.
     *
     * @return Factory
     */
    public function type(int $typeId)
    {
        return $this->state(function (array $attributes) use ($typeId) {
            return [
                'area_type_id' => $typeId,
            ];
        });
    }

    /**
     * Indicate the area creator.
     *
     * @return Factory
     */
    public function creator(int $userId)
    {
        return $this->state(function (array $attributes) use ($userId) {
            return [
                'created_by' => $userId,
            ];
        });
    }
}
