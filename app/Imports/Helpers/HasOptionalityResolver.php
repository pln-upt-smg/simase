<?php

namespace App\Imports\Helpers;

use Illuminate\Support\Str;

trait HasOptionalityResolver
{
    /**
     * @param string|null $optionality
     * @return bool
     */
    public function resolveOptionality(?string $optionality): bool
    {
        return trim(Str::lower($optionality ?? '')) === 'ada';
    }
}
