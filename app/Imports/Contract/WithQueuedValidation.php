<?php

namespace App\Imports\Contract;

interface WithQueuedValidation
{
	/**
	 * @return array
	 */
	public function validation(): array;
}
