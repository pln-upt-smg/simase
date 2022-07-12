<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AssetSubmissionImage;

class AssetSubmissionImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AssetSubmissionImage::class;

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
     * Indicate the image's asset submission.
     *
     * @return Factory
     */
    public function assetSubmission(int $assetSubmissionId)
    {
        return $this->state(function (array $attributes) use (
            $assetSubmissionId
        ) {
            return [
                'asset_submission_id' => $assetSubmissionId,
            ];
        });
    }
}
