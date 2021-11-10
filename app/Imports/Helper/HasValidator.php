<?php

namespace App\Imports\Helper;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait HasValidator
{
    /**
     * @throws ValidationException
     */
    public function validate(array $row, array $rules = []): void
    {
        Validator::validate($row, function_exists('validation') ? $this->validation() : $rules);
    }
}
