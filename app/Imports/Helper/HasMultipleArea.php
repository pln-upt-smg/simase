<?php

namespace App\Imports\Helper;

trait HasMultipleArea
{
	use HasAreaResolver;

	/**
	 * @var int
	 */
	private int $currentAreaId = 0;

	/**
	 * @param array $row
	 * @param bool $bySloc
	 */
	public function lookupArea(array $row, bool $bySloc = false): void
	{
		$newAreaId = $this->resolveAreaId($row['area'] ?? $row['sloc'], $bySloc);
		if ($this->currentAreaId !== $newAreaId) {
			$this->currentAreaId = $newAreaId;
		}
	}
}
