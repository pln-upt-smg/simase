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
    AssetLossDamageImage,
    AssetTransfer,
    AssetTransferImage
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

        AssetType::updateOrCreate([
            'created_by' => 1,
            'name' => 'Kabel Tembaga',
        ]);
        AssetType::updateOrCreate([
            'created_by' => 1,
            'name' => 'Terminal Listrik',
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
                                AssetLossDamageImage::factory()
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
                    ->has(
                        AssetTransfer::factory()
                            ->creator(1)
                            ->state(function (array $attributes, Asset $asset) {
                                return ['asset_id' => $asset->id];
                            })
                            ->has(
                                AssetTransferImage::factory()
                                    ->count(3)
                                    ->state(function (
                                        array $attributes,
                                        AssetTransfer $assetTransfer
                                    ) {
                                        return [
                                            'asset_transfer_id' =>
                                                $assetTransfer->id,
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
                                AssetLossDamageImage::factory()
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
