<?php

namespace App\Imports\Helpers;

use App\Models\District;

trait HasDistrictResolver
{
	/**
	 * @param string|null $districtName
	 * @return int
	 */
	public function resolveDistrictId(?string $districtName): int
	{
		$districtName = $districtName ?? '';
		return District::whereRaw('lower(name) = lower(?)', trim($districtName))->whereNull('deleted_at')->first()?->id ?? 0;
	}
}
