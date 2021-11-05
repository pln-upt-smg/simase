<?php

namespace App\Imports\Helper;

use App\Models\Area;

trait HasAreaResolver
{
    protected function resolveAreaId(string $areaName): int
    {
        return Area::whereRaw('lower(code) = lower(?)', trim($areaName))->whereNull('deleted_at')->first()?->id ?? 0;
    }
}
