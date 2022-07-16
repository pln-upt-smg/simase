<?php

namespace App\Imports\Helpers;

trait HasChunkSize
{
	/**
	 * @return int
	 */
	public function chunkSize(): int
	{
		return 5000;
	}
}
