<?php

namespace App\Imports\Helper;

trait HasBatchSize
{
    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 100;
    }
}
