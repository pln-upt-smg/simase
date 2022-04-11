<?php

namespace App\Imports\Helper;

use App\Models\Material;

trait HasMaterialResolver
{
	/**
	 * @param string|null $materialCode
	 * @return int
	 */
	    public function resolveMaterialId(?string $materialCode, ?int $periodId = null): int
    {
        $materialCode = $materialCode ?? '';
        $material = Material::whereRaw('lower(code) = lower(?)', trim($materialCode))->whereNull('deleted_at');

        if ($periodId) {
            $material->where('period_id', $periodId);
        }

        return $material->first()?->id ?? 0;
    }
}
