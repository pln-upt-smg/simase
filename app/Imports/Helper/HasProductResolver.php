<?php

namespace App\Imports\Helper;

use App\Models\Product;

trait HasProductResolver
{
	/**
	 * @param string|null $productCode
	 * @return int
	 */
	public function resolveProductId(?string $productCode, ?int $periodId): int
	{
		$productCode = $productCode ?? '';
		$product = Product::whereRaw('lower(code) = lower(?)', trim($productCode))->whereNull('deleted_at');

	        if ($periodId) {
	            $product->where('period_id', $periodId);
        	}

	        return $product->first()?->id ?? 0;
	}
}
