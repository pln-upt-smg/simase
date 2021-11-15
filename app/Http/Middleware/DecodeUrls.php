<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class DecodeUrls extends TransformsRequest
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array
     */
    protected array $except = [
        //
    ];

    /**
     * Transform the given value.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function transform($key, $value): mixed
    {
        if (in_array($key, $this->except, true)) {
            return $value;
        }
        return filter_var($value, FILTER_VALIDATE_URL) ? urldecode($value) : $value;
    }
}
