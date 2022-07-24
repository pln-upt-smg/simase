<?php

namespace App\Imports\Helpers;

use App\Models\Holder;

trait HasHolderResolver
{
	/**
	 * @param string|null $holderName
	 * @return int
	 */
	public function resolveHolderId(?string $holderName): int
	{
		$holderName = $holderName ?? '';
		return Holder::whereRaw('lower(name) = lower(?)', trim($holderName))->whereNull('deleted_at')->first()?->id ?? 0;
	}
}
