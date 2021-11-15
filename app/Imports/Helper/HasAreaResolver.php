<?php

namespace App\Imports\Helper;

use App\Models\Area;

trait HasAreaResolver
{
    /**
     * @param string|null $area
     * @param bool $bySloc
     * @return int
     */
    public function resolveAreaId(?string $area, bool $bySloc = false): int
    {
        $area = $area ?? '';
        if ($bySloc) {
            return Area::whereRaw('lower(sloc) = lower(?)', trim($area))->whereNull('deleted_at')->first()?->id ?? 0;
        }
        return Area::whereRaw('lower(name) = lower(?)', trim($area))->whereNull('deleted_at')->first()?->id ?? 0;
    }
}
