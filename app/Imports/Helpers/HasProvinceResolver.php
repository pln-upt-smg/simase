<?php

namespace App\Imports\Helpers;

use App\Models\Province;

trait HasProvinceResolver
{
	/**
	 * @param string|null $provinceName
	 * @return int
	 */
	public function resolveProvinceId(?string $provinceName): int
	{
		$provinceName = $provinceName ?? '';
		return Province::whereRaw('lower(name) = lower(?)', trim($provinceName))->whereNull('deleted_at')->first()?->id ?? 0;
	}
}
