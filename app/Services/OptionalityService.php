<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class OptionalityService
{
    /**
     * @param Request $request
     * @return string
     */
    public function resolve(Request $request): string
    {
        if (
            $request->query('optionality') === '1' ||
            $request->query('optionality') === 1
        ) {
            return $this->collection()[0];
        }
        if (
            $request->query('optionality') === '2' ||
            $request->query('optionality') === 2
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
                'name' => 'Ya',
            ],
            [
                'id' => 0,
                'name' => 'Tidak',
            ],
        ]);
    }
}
