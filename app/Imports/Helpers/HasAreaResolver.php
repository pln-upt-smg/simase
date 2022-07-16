<?php

namespace App\Imports\Helpers;

use App\Models\Area;

trait HasAreaResolver
{
	/**
	 * @param string|null $area
	 * @param bool $byCode
	 * @return int
	 */
	public function resolveAreaId(?string $area, bool $byCode = false): int
	{
		$area = $area ?? '';
		if ($byCode) {
			return Area::whereRaw('lower(code) = lower(?)', trim($area))->whereNull('deleted_at')->first()?->id ?? 0;
		}
		return Area::whereRaw('lower(name) = lower(?)', trim($area))->whereNull('deleted_at')->first()?->id ?? 0;
	}
}
