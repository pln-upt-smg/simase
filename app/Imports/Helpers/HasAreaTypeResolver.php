<?php

namespace App\Imports\Helpers;

use App\Models\AreaType;

trait HasAreaTypeResolver
{
	/**
	 * @param string|null $areaType
	 * @return int
	 */
	public function resolveAreaTypeId(?string $areaType): int
	{
		return AreaType::whereRaw('lower(name) = lower(?)', trim($areaType ?? ''))->whereNull('deleted_at')->first()?->id ?? 0;
	}
}
