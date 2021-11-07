<?php

namespace App\Imports;

use App\Imports\Contract\WithDefaultEvents;
use App\Imports\Helper\HasBatchSize;
use App\Imports\Helper\HasChunkSize;
use App\Imports\Helper\HasDefaultEvents;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasImporter;
use App\Models\Role;
use App\Models\User;
use App\Rules\IsValidDigit;
use App\Rules\IsValidPhone;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithUpserts;

class OperatorsImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithMultipleSheets, WithChunkReading, WithBatchInserts, WithUpserts, WithEvents, WithDefaultEvents, ShouldQueue, ShouldBeUnique
{
    use HasDefaultSheet, HasDefaultEvents, HasImporter, HasChunkSize, HasBatchSize;

    private int $roleId;

    public function __construct(?User $user)
    {
        $this->roleId = Role::operator()?->id ?? 2;
        $this->userId = $user?->id ?? 0;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'max:255'],
            'phone' => ['nullable', 'max:20', new IsValidPhone],
            'nip' => ['required', 'numeric', new IsValidDigit(6)],
            'role' => ['required', 'max:255', Rule::exists('roles', 'name')->whereNull('deleted_at')]
        ];
    }

    public function uniqueBy(): string|array
    {
        return [
            'phone',
            'nip'
        ];
    }

    public function model(array $row): ?User
    {
        return new User([
            'role_id' => $this->role?->id ?? 2,
            'name' => Str::title(trim($row['name'])),
            'phone' => trim($row['phone']),
            'nip' => trim($row['nip']),
            'password' => Hash::make(trim($row['nip']))
        ]);
    }

    public function name(): string
    {
        return 'Pegawai';
    }

    public function overwrite(): void
    {
        User::leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->where('roles.id', $this->roleId)
            ->whereNull('users.deleted_at')
            ->delete();
    }
}
