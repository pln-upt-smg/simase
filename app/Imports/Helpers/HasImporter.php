<?php

namespace App\Imports\Helpers;

use App\Models\User;

trait HasImporter
{
	/**
	 * @var int
	 */
	private int $userId;

	/**
	 * @return User|null
	 */
	public function importedBy(): ?User
	{
		return User::where('id', $this->userId)->first();
	}
}
