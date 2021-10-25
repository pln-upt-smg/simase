<?php

namespace App\Imports\Helper;

trait HasDefaultSheet
{
    /**
     * @return array
     */
    public function sheets(): array
    {
        $default = trim(config('excel.default_sheet'));
        return [$default => $this];
    }
}
