<?php

namespace Database\Factories;

use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MaterialFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Material::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'area_id' => 1,
            'period_id' => 1,
            'code' => 'M' . Str::upper(Str::random(5)),
            'description' => 'Material ' . $this->faker->unique->colorName(),
            'uom' => 'KG',
            'mtyp' => 'ROH',
            'crcy' => 'IDR',
            'price' => $this->faker->numberBetween(10000, 100000),
            'per' => 1
        ];
    }
}
