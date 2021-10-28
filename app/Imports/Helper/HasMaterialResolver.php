<?php

namespace App\Imports\Helper;

use App\Models\Material;

trait HasMaterialResolver
{
    protected function resolveMaterialId(string $materialCode): int
    {
        return Material::whereRaw('lower(code) = lower(?)', trim($materialCode))->whereNull('deleted_at')->first()?->id ?? 0;
    }
}
