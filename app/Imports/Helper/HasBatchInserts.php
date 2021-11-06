<?php

namespace App\Imports\Helper;

trait HasBatchInserts
{
    public function batchSize(): int
    {
        return 500;
    }
}
