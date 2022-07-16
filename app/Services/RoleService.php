<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RoleService
{
    /**
     * @param Request $request
     * @return Role|null
     */
    public function resolve(Request $request): ?Role
    {
        if ($request->query('role') === '0' || $request->query('role') === 0) {
            return null;
        }
        return Role::where(
            'id',
            $request->query('role') ? (int) $request->query('role') : 0
        )->first();
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return Role::orderBy('name')->get();
    }
}
