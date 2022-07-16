<?php

namespace App\Imports\Helpers;

use App\Models\AssetType;

trait HasAssetTypeResolver
{
	/**
	 * @param string|null $areaType
	 * @return int
	 */
	public function resolveAssetTypeId(?string $assetType): int
	{
		return AssetType::whereRaw('lower(name) = lower(?)', trim($assetType ?? ''))->whereNull('deleted_at')->first()?->id ?? 0;
	}
}
