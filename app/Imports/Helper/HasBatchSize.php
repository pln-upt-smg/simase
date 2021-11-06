<?php

namespace App\Imports\Helper;

trait HasBatchSize
{
    public function batchSize(): int
    {
        return 500;
    }
}
