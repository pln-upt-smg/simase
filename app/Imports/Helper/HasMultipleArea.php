<?php

namespace App\Imports\Helper;

trait HasMultipleArea
{
    use HasAreaResolver;

    /**
     * @var int
     */
    private int $currentAreaId = 0;

    /**
     * @param array $row
     */
    public function lookupArea(array $row): void
    {
        $newAreaId = $this->resolveAreaId($row['area']);
        if ($this->currentAreaId !== $newAreaId) {
            $this->currentAreaId = $newAreaId;
        }
    }
}
