<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AssetSubmission;

class AssetSubmissionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AssetSubmission::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'note' => $this->faker->paragraph(),
            'quantity' => $this->faker->numberBetween(1, 100),
            'priority' => $this->faker->numberBetween(1, 3),
        ];
    }

    /**
     * Indicate the asset submission's asset.
     *
     * @return Factory
     */
    public function asset(int $assetId)
    {
        return $this->state(function (array $attributes) use ($assetId) {
            return [
                'asset_id' => $assetId,
            ];
        });
    }

    /**
     * Indicate the asset submission creator.
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

    /**
     * Indicate that the asset submission priority is low.
     *
     * @return Factory
     */
    public function lowPriority()
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => 1,
            ];
        });
    }

    /**
     * Indicate that the asset submission priority is medium.
     *
     * @return Factory
     */
    public function mediumPriority()
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => 2,
            ];
        });
    }

    /**
     * Indicate that the asset submission priority is high.
     *
     * @return Factory
     */
    public function highPriority()
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => 3,
            ];
        });
    }
}
