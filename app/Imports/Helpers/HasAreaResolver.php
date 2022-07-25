<?php

namespace App\Imports\Helpers;

use App\Models\Area;

trait HasAreaResolver
{
	/**
	 * @param string|null $area
	 * @param bool $byFuncloc
	 * @return int
	 */
	public function resolveAreaId(?string $area, bool $byFuncloc = false): int
	{
		$area = $area ?? '';
		if ($byFuncloc) {
			return Area::whereRaw('lower(funcloc) = lower(?)', trim($area))->whereNull('deleted_at')->first()?->id ?? 0;
		}
		return Area::whereRaw('lower(name) = lower(?)', trim($area))->whereNull('deleted_at')->first()?->id ?? 0;
	}
}
