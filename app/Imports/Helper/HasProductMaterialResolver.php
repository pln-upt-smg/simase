<?php

namespace App\Imports\Helper;

use App\Models\ProductMaterial;

trait HasProductMaterialResolver
{
	/**
	 * @param int $productId
	 * @param int $materialId
	 * @return int
	 */
	public function resolveProductMaterialId(int $productId, int $materialId): int
	{
		return ProductMaterial::where('product_id', $productId)->where('material_id', $materialId)->whereNull('deleted_at')->first()?->id ?? 0;
	}
}
