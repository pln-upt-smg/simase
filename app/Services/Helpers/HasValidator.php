<?php

namespace App\Services\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait HasValidator
{
    /**
     * @throws ValidationException
     */
    public function validate(
        Request $request,
        array $rules,
        array $messages = [],
        array $attributes = []
    ): void {
        Validator::validate($request->all(), $rules, $messages, $attributes);
    }
}
