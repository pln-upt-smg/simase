<?php

namespace App\Imports\Contract;

use App\Models\User;

interface WithDefaultEvents
{
	/**
	 * @return string
	 */
	public function name(): string;

	/**
	 * @return void
	 */
	public function overwrite(): void;

	/**
	 * @return User|null
	 */
	public function importedBy(): ?User;
}
