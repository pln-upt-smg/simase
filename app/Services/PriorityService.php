<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PriorityService
{
    /**
     * @param Request $request
     * @return string
     */
    public function resolve(Request $request): string
    {
        if (
            $request->query('priority') === '1' ||
            $request->query('priority') === 1
        ) {
            return $this->collection()[0];
        }
        if (
            $request->query('priority') === '2' ||
            $request->query('priority') === 2
        ) {
            return $this->collection()[1];
        }
        return $this->collection()[2];
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return collect([
            [
                'id' => 1,
                'name' => 'Rendah',
            ],
            [
                'id' => 2,
                'name' => 'Sedang',
            ],
            [
                'id' => 3,
                'name' => 'Tinggi',
            ],
        ]);
    }
}
