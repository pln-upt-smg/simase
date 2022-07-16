<?php

namespace App\Imports\Helpers;

use App\Models\SubArea;

trait HasSubAreaResolver
{
	/**
	 * @param string|null $subarea
	 * @param bool $bySloc
	 * @return int
	 */
	public function resolveSubAreaId(?string $subarea, bool $bySloc = false): int
	{
		$subarea = $subarea ?? '';
		if ($bySloc) {
			return SubArea::whereRaw('lower(sloc) = lower(?)', trim($subarea))->whereNull('deleted_at')->first()?->id ?? 0;
		}
		return SubArea::whereRaw('lower(name) = lower(?)', trim($subarea))->whereNull('deleted_at')->first()?->id ?? 0;
	}
}
