<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'period_id' => 1,
            'code' => 'P' . Str::upper(Str::random(5)),
            'description' => 'Product ' . $this->faker->unique->colorName(),
            'uom' => 'KG',
            'mtyp' => 'ROH',
            'crcy' => 'IDR',
            'price' => $this->faker->numberBetween(10000, 100000),
            'per' => 1
        ];
    }
}
