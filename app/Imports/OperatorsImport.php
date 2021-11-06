<?php

namespace App\Imports;

use App\Imports\Helper\HasBatchSize;
use App\Imports\Helper\HasChunkSize;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasRowCounter;
use App\Imports\Helper\HasValidationException;
use App\Models\Role;
use App\Models\User;
use App\Rules\IsValidDigit;
use App\Rules\IsValidPhone;
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
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\BeforeSheet;

class OperatorsImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithValidation, WithEvents, WithMultipleSheets, WithBatchInserts, WithChunkReading, WithUpserts
{
    use HasValidationException, HasDefaultSheet, HasRowCounter, HasBatchSize, HasChunkSize;

    private ?Role $role;

    public function __construct()
    {
        $this->role = Role::operator();
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
        $this->incrementRowCounter();
        return new User([
            'role_id' => $this->role?->id ?? 2,
            'name' => Str::title(trim($row['name'])),
            'phone' => trim($row['phone']),
            'nip' => trim($row['nip']),
            'password' => Hash::make(trim($row['nip']))
        ]);
    }

    public function registerEvents(): array
    {
        $role = $this->role;
        return [
            BeforeSheet::class => static function () use ($role) {
                User::leftJoin('roles', 'roles.id', '=', 'users.role_id')
                    ->where('roles.id', $role?->id ?? 2)
                    ->whereNull('users.deleted_at')
                    ->delete();
            }
        ];
    }
}
