<?php

namespace App\Imports\Helper;

use App\Models\Product;

trait HasProductResolver
{
    protected function resolveProductId(string $productCode): int
    {
        return Product::whereRaw('lower(code) = lower(?)', trim($productCode))->whereNull('deleted_at')->first()?->id ?? 0;
    }
}
