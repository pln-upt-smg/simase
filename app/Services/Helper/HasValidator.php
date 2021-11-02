<?php

namespace App\Services\Helper;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait HasValidator
{
    /**
     * @throws ValidationException
     */
    public function validate(Request $request, array $rules, array $messages = [], array $attributes = []): void
    {
        Validator::make($request->all(), $rules, $messages, $attributes)->validate();
    }
}
