<?php

namespace App\Imports\Helper;

trait HasChunkSize
{
    public function chunkSize(): int
    {
        return 1000;
    }
}
