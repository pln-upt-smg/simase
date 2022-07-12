<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Asset;

class AssetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Asset::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => Str::title($this->faker->unique->words(1, 1)),
            'quantity' => $this->faker->numberBetween(1, 100),
        ];
    }

    /**
     * Indicate the asset type.
     *
     * @return Factory
     */
    public function type(int $typeId)
    {
        return $this->state(function (array $attributes) use ($typeId) {
            return [
                'asset_type_id' => $typeId,
            ];
        });
    }

    /**
     * Indicate the asset area.
     *
     * @return Factory
     */
    public function area(int $areaId)
    {
        return $this->state(function (array $attributes) use ($areaId) {
            return [
                'area_id' => $areaId,
            ];
        });
    }

    /**
     * Indicate the asset creator.
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
