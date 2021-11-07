<?php

namespace App\Imports\Helper;

trait HasChunkSize
{
    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 500;
    }
}
