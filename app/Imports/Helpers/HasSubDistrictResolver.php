<?php

namespace App\Imports\Helpers;

use App\Models\SubDistrict;

trait HasSubDistrictResolver
{
	/**
	 * @param string|null $subDistrictName
	 * @return int
	 */
	public function resolveSubDistrictId(?string $subDistrictName): int
	{
		$subDistrictName = $subDistrictName ?? '';
		return SubDistrict::whereRaw('lower(name) = lower(?)', trim($subDistrictName))->whereNull('deleted_at')->first()?->id ?? 0;
	}
}
