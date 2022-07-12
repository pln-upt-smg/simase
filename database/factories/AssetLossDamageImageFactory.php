<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AssetLossDamageImage;

class AssetLossDamageImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AssetLossDamageImage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'image' => $this->faker->imageUrl(500, 500, null, true),
        ];
    }

    /**
     * Indicate the image's asset loss damage.
     *
     * @return Factory
     */
    public function assetLossDamage(int $assetLossDamageId)
    {
        return $this->state(function (array $attributes) use (
            $assetLossDamageId
        ) {
            return [
                'asset_loss_damage_id' => $assetLossDamageId,
            ];
        });
    }
}
