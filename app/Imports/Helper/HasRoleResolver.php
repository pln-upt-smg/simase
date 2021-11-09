<?php

namespace App\Imports\Helper;

use App\Models\Role;

trait HasRoleResolver
{
    /**
     * @param string $roleName
     * @return int
     */
    public function resolveRoleId(string $roleName): int
    {
        return Role::whereRaw('lower(name) = lower(?)', trim($roleName))->whereNull('deleted_at')->first()?->id ?? 0;
    }
}
