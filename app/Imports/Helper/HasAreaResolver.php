<?php

namespace App\Imports\Helper;

use App\Models\Area;

trait HasAreaResolver
{
    /**
     * @param string $areaName
     * @return int
     */
    public function resolveAreaId(string $areaName): int
    {
        return Area::whereRaw('lower(name) = lower(?)', trim($areaName))->whereNull('deleted_at')->first()?->id ?? 0;
    }
}
