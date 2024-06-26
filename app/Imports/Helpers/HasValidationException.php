<?php

namespace App\Imports\Helpers;

use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Validators\Failure;

trait HasValidationException
{
	/**
	 * @throws ValidationException
	 */
	public function onFailure(Failure ...$failures): void
	{
		throw ValidationException::withMessages(collect($failures)->map->toArray()->all());
	}
}
