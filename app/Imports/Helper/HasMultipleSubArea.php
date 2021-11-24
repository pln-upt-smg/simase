<?php

namespace App\Imports\Helper;

trait HasMultipleSubArea
{
	use HasSubAreaResolver;

	/**
	 * @var int
	 */
	private int $currentSubAreaId = 0;

	/**
	 * @param array $row
	 * @param bool $bySloc
	 */
	public function lookupSubArea(array $row, bool $bySloc = false): void
	{
		$newSubAreaId = $this->resolveSubAreaId($row['subarea'], $bySloc);
		if ($this->currentSubAreaId !== $newSubAreaId) {
			$this->currentSubAreaId = $newSubAreaId;
		}
	}
}
