<?php

namespace App\Imports;

use App\Imports\Contracts\WithDefaultEvents;
use App\Imports\Helpers\{
    HasBatchSize,
    HasChunkSize,
    HasDefaultEvents,
    HasDefaultSheet,
    HasImporter,
    HasRoleResolver
};
use App\Models\User;
use App\Rules\{IsValidDigit, IsValidPhone};
use Illuminate\Contracts\Queue\{ShouldBeUnique, ShouldQueue};
use Illuminate\Support\{Collection, Str};
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\{
    Importable,
    SkipsEmptyRows,
    SkipsErrors,
    SkipsFailures,
    SkipsOnError,
    SkipsOnFailure,
    ToCollection,
    WithBatchInserts,
    WithChunkReading,
    WithEvents,
    WithHeadingRow,
    WithMultipleSheets,
    WithUpserts,
    WithValidation
};

class EmployeeImport implements
    ToCollection,
    SkipsOnFailure,
    SkipsOnError,
    SkipsEmptyRows,
    WithHeadingRow,
    WithMultipleSheets,
    WithChunkReading,
    WithBatchInserts,
    WithUpserts,
    WithEvents,
    WithDefaultEvents,
    WithValidation,
    ShouldQueue,
    ShouldBeUnique
{
    use Importable,
        SkipsFailures,
        SkipsErrors,
        HasDefaultSheet,
        HasDefaultEvents,
        HasImporter,
        HasChunkSize,
        HasBatchSize,
        HasRoleResolver;

    public function __construct(?User $user)
    {
        if (is_null($user)) {
            $this->userId = 0;
        } else {
            $this->userId = $user->id;
        }
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:20', new IsValidPhone()],
            'nip' => ['required', 'numeric', new IsValidDigit(6)],
            'peran' => [
                'required',
                'string',
                'max:255',
                Rule::exists('roles', 'name')->whereNull('deleted_at'),
            ],
        ];
    }

    public function uniqueBy()
    {
        return ['telepon', 'nip'];
    }

    public function collection(Collection $collection): void
    {
        foreach ($collection as $row) {
            $this->replace($row->toArray());
        }
    }

    public function name(): string
    {
        return 'Pegawai';
    }

    public function replace(array $row): void
    {
        $roleId = $this->resolveRoleId($row['role']);
        if ($roleId === 0) {
            return;
        }
        User::updateOrCreate(
            [
                'nip' => trim($row['nip']),
            ],
            [
                'role_id' => $roleId,
                'name' => Str::title(trim($row['name'])),
                'phone' => trim($row['phone']),
                'password' => Hash::make(trim($row['nip'])),
            ]
        );
    }

    public function overwrite(): void
    {
        User::leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->where('users.id', '<>', $this->userId)
            ->whereNull('users.deleted_at')
            ->delete();
    }
}
