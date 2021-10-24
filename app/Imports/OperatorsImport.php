<?php

namespace App\Imports;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class OperatorsImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithValidation, WithEvents, WithMultipleSheets
{
    use RegistersEventListeners;

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'nomor_telepon' => ['required', 'string', 'max:20'],
            'nip' => ['required', 'alpha_num', 'min:6', 'max:255'],
            'peran' => ['required', 'string', 'max:255', 'exists:roles,name']
        ];
    }

    public function uniqueBy(): string|array
    {
        return [
            'nomor_telepon',
            'nip'
        ];
    }

    public function model(array $row): User|null
    {
        return new User([
            'role_id' => $this->resolveRoleId($row['peran']),
            'name' => $row['nama'],
            'phone' => $row['nomor_telepon'],
            'nip' => $row['nip'],
            'password' => Hash::make($row['nip'])
        ]);
    }

    protected function resolveRoleId(string $roleName): int
    {
        $role = Role::whereName(Str::title(trim($roleName)))->first();
        return is_null($role) ? 2 : $role->id;
    }

    /**
     * @throws ValidationException
     */
    public function onFailure(Failure ...$failures): void
    {
        throw ValidationException::withMessages(collect($failures)->map->toArray()->all());
    }

    public static function beforeSheet(): void
    {
        User::whereRoleId(Role::operator()?->id ?? 2)->delete();
    }

    public function sheets(): array
    {
        return [
            'Worksheet' => $this
        ];
    }
}
