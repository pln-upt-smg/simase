<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Certificate;

class CertificateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Certificate::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'urban_village_id' => $this->faker->numberBetween(1, 5),
            'sub_district_id' => $this->faker->numberBetween(1, 5),
            'district_id' => $this->faker->numberBetween(1, 5),
            'province_id' => $this->faker->numberBetween(1, 5),
            'holder_id' => $this->faker->numberBetween(1, 5),
            'name' => Str::title($this->faker->unique->word),
            'area_code' => $this->faker->unique->numberBetween(1111, 9999),
            'certificate_type' => $this->faker->randomElement(['HGB', 'HP']),
            'certificate_number' => $this->faker->unique->numberBetween(
                1111,
                9999
            ),
            'certificate_print_number' => $this->faker->unique->numberBetween(
                1111,
                9999
            ),
            'certificate_bookkeeping_date' => $this->faker->dateTime,
            'certificate_publishing_date' => $this->faker->dateTime,
            'certificate_final_date' => $this->faker->dateTime,
            'nib' => $this->faker->unique->numberBetween(1111, 9999),
            'origin_right_category' => 'Pemberian Hak',
            'base_registration_decree_number' => $this->faker->unique->numberBetween(
                1111,
                9999
            ),
            'base_registration_date' => $this->faker->dateTime,
            'measuring_letter_number' => $this->faker->unique->numberBetween(
                1111,
                9999
            ),
            'measuring_letter_date' => $this->faker->dateTime,
            'measuring_letter_status' => $this->faker->boolean,
            'field_map_status' => $this->faker->boolean,
            'wide' => $this->faker->unique->numberBetween(10, 1000),
        ];
    }

    /**
     * Indicate the asset loss damage creator.
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
