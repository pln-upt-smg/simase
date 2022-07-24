<?php

namespace App\Imports\Helpers;

use App\Models\Division;

trait HasDivisionResolver
{
	/**
	 * @param string|null $divisionName
	 * @return int
	 */
	public function resolveDivisionId(?string $divisionName): int
	{
		$divisionName = $divisionName ?? '';
		return Division::whereRaw('lower(name) = lower(?)', trim($divisionName))->whereNull('deleted_at')->first()?->id ?? 0;
	}
}
