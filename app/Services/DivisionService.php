<?php

namespace App\Services;

use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DivisionService
{
    /**
     * @param Request $request
     * @return Division|null
     */
    public function resolve(Request $request): ?Division
    {
        if (
            $request->query('division') === '0' ||
            $request->query('division') === 0
        ) {
            return null;
        }
        return Division::where(
            'id',
            $request->query('division') ? (int) $request->query('division') : 0
        )->first();
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return Division::orderBy('name')->get();
    }
}
