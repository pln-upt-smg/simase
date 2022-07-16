<?php

namespace App\Imports\Helpers;

use App\Models\Asset;

trait HasAssetResolver
{
	/**
	 * @param string|null $asset
	 * @param bool $byCode
	 * @return int
	 */
	public function resolveAssetId(?string $asset): int
	{
		return Asset::whereRaw('lower(name) = lower(?)', trim($asset ?? ''))->whereNull('deleted_at')->first()?->id ?? 0;
	}
}
