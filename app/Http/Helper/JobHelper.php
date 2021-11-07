<?php

namespace App\Http\Helper;

use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;

abstract class JobHelper
{
    /**
     * @throws ValidationException
     */
    public static function limitOnce(): void
    {
        if (Queue::size('default') > 0) {
            throw ValidationException::withMessages(['status' => 'Sudah ada proses impor data yang berjalan di latar belakang, mohon coba lagi nanti.']);
        }
    }
}
