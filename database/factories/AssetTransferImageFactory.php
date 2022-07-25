<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AssetTransferImage;

class AssetTransferImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AssetTransferImage::class;

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
     * Indicate the image's asset transfer.
     *
     * @return Factory
     */
    public function assetTransfer(int $assetTransferId)
    {
        return $this->state(function (array $attributes) use (
            $assetTransferId
        ) {
            return [
                'asset_transfer_id' => $assetTransferId,
            ];
        });
    }
}
