<?php

namespace App\Imports\Helper;

trait HasRowCounter
{
    private int $rowCount = 0;

    private function incrementRowCounter(): void
    {
        $this->rowCount++;
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}
