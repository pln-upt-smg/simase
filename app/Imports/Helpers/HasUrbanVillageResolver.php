<?php

namespace App\Imports\Helpers;

use App\Models\UrbanVillage;

trait HasUrbanVillageResolver
{
	/**
	 * @param string|null $urbanVillageName
	 * @return int
	 */
	public function resolveUrbanVillageId(?string $urbanVillageName): int
	{
		$urbanVillageName = $urbanVillageName ?? '';
		return UrbanVillage::whereRaw('lower(name) = lower(?)', trim($urbanVillageName))->whereNull('deleted_at')->first()?->id ?? 0;
	}
}
