<?php

namespace Database\Seeders\Data;

use Illuminate\Database\Seeder;
use App\Models\{
    Area,
    Asset,
    AssetType,
    AssetSubmission,
    AssetSubmissionImage,
    AssetLossDamage,
    AssetLossDamageImage
};

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        if (app()->isProduction() && !config('app.env_staging')) {
            return;
        }

        AssetType::create([
            'created_by' => 1,
            'name' => 'Kabel Tembaga',
            'uom' => 'M',
        ]);
        AssetType::create([
            'created_by' => 1,
            'name' => 'Terminal Listrik',
            'uom' => 'PCS',
        ]);

        Area::factory()
            ->count(2)
            ->type(1)
            ->creator(1)
            ->has(
                Asset::factory()
                    ->count(4)
                    ->type(1)
                    ->creator(1)
                    ->state(function (array $attributes, Area $area) {
                        return ['area_id' => $area->id];
                    })
                    ->has(
                        AssetSubmission::factory()
                            ->creator(1)
                            ->state(function (array $attributes, Asset $asset) {
                                return ['asset_id' => $asset->id];
                            })
                            ->has(
                                AssetSubmissionImage::factory()
                                    ->count(3)
                                    ->state(function (
                                        array $attributes,
                                        AssetSubmission $assetSubmission
                                    ) {
                                        return [
                                            'asset_submission_id' =>
                                                $assetSubmission->id,
                                        ];
                                    })
                            )
                    )
                    ->has(
                        AssetLossDamage::factory()
                            ->creator(1)
                            ->state(function (array $attributes, Asset $asset) {
                                return ['asset_id' => $asset->id];
                            })
                            ->has(
                                AssetLossDamage::factory()
                                    ->count(3)
                                    ->state(function (
                                        array $attributes,
                                        AssetLossDamage $assetLossDamage
                                    ) {
                                        return [
                                            'asset_loss_damage_id' =>
                                                $assetLossDamage->id,
                                        ];
                                    })
                            )
                    )
            )
            ->create();

        Area::factory()
            ->count(2)
            ->type(2)
            ->creator(1)
            ->has(
                Asset::factory()
                    ->count(4)
                    ->type(2)
                    ->creator(1)
                    ->state(function (array $attributes, Area $area) {
                        return ['area_id' => $area->id];
                    })
                    ->has(
                        AssetSubmission::factory()
                            ->creator(1)
                            ->state(function (array $attributes, Asset $asset) {
                                return ['asset_id' => $asset->id];
                            })
                            ->has(
                                AssetSubmissionImage::factory()
                                    ->count(3)
                                    ->state(function (
                                        array $attributes,
                                        AssetSubmission $assetSubmission
                                    ) {
                                        return [
                                            'asset_submission_id' =>
                                                $assetSubmission->id,
                                        ];
                                    })
                            )
                    )
                    ->has(
                        AssetLossDamage::factory()
                            ->creator(1)
                            ->state(function (array $attributes, Asset $asset) {
                                return ['asset_id' => $asset->id];
                            })
                            ->has(
                                AssetLossDamage::factory()
                                    ->count(3)
                                    ->state(function (
                                        array $attributes,
                                        AssetLossDamage $assetLossDamage
                                    ) {
                                        return [
                                            'asset_loss_damage_id' =>
                                                $assetLossDamage->id,
                                        ];
                                    })
                            )
                    )
            )
            ->create();
    }
}
