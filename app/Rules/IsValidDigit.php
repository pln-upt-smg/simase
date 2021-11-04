<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class IsValidDigit implements Rule
{
    private int $minDigit;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(int $minDigit)
    {
        $this->minDigit = $minDigit;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return Str::length((string)$value) >= $this->minDigit;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return ':attribute minimal berisi ' . $this->minDigit . ' digit.';
    }
}
