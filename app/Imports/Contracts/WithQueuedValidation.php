<?php

namespace App\Imports\Contracts;

interface WithQueuedValidation
{
	/**
	 * @return array
	 */
	public function validation(): array;
}
