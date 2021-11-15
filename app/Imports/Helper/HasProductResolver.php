<?php

namespace App\Imports\Helper;

use App\Models\Product;

trait HasProductResolver
{
    /**
     * @param string|null $productCode
     * @return int
     */
    public function resolveProductId(?string $productCode): int
    {
        $productCode = $productCode ?? '';
        return Product::whereRaw('lower(code) = lower(?)', trim($productCode))->whereNull('deleted_at')->first()?->id ?? 0;
    }
}
