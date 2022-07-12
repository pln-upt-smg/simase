<?php

namespace App\Models\Helpers;

trait HasPriority
{
    public function isPriorityLow(): bool
    {
        return $this->id === 1;
    }

    public function isPriorityMedium(): bool
    {
        return $this->id === 2;
    }

    public function isPriorityHigh(): bool
    {
        return !$this->isPriorityLow() && !$this->isPriorityMedium();
    }
}
