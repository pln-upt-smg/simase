<?php

namespace App\Imports\Helper;

trait HasDefaultSheet
{
    /**
     * @return array
     */
    public function sheets(): array
    {
        return [config('excel.default_sheet') => $this];
    }
}
