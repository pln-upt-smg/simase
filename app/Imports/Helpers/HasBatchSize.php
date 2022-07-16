<?php

namespace App\Imports\Helpers;

trait HasBatchSize
{
	/**
	 * @return int
	 */
	public function batchSize(): int
	{
		return 100;
	}
}
