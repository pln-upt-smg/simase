<?php

namespace App\Imports;

use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasValidationException;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;

class OperatorsImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithValidation, WithEvents, WithMultipleSheets
{
    use HasValidationException, HasDefaultSheet, RegistersEventListeners;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'nip' => ['required', 'alpha_num', 'min:6', 'max:255'],
            'role' => ['required', 'string', 'max:255', 'exists:roles,name']
        ];
    }

    public function uniqueBy(): string|array
    {
        return [
            'phone',
            'nip'
        ];
    }

    public function model(array $row): User|null
    {
        return new User([
            'role_id' => $this->resolveRoleId($row['role']),
            'name' => Str::title(trim($row['name'])),
            'phone' => trim($row['phone']),
            'nip' => trim($row['nip']),
            'password' => Hash::make(trim($row['nip']))
        ]);
    }

    protected function resolveRoleId(string $roleName): int
    {
        $role = Role::whereName(Str::title(trim($roleName)))->first();
        return is_null($role) ? 2 : $role->id;
    }

    public static function beforeSheet(): void
    {
        User::whereRoleId(Role::operator()?->id ?? 2)->delete();
    }
}
