<?php

namespace App\Imports;

use App\Imports\Contract\WithDefaultEvents;
use App\Imports\Contract\WithQueuedValidation;
use App\Imports\Helper\HasBatchSize;
use App\Imports\Helper\HasChunkSize;
use App\Imports\Helper\HasDefaultEvents;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasImporter;
use App\Imports\Helper\HasRoleResolver;
use App\Imports\Helper\HasValidator;
use App\Models\User;
use App\Rules\IsValidDigit;
use App\Rules\IsValidPhone;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithUpserts;

class EmployeesImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithMultipleSheets, WithChunkReading, WithBatchInserts, WithUpserts, WithEvents, WithDefaultEvents, WithQueuedValidation, ShouldQueue, ShouldBeUnique
{
    use HasDefaultSheet, HasDefaultEvents, HasImporter, HasChunkSize, HasBatchSize, HasValidator, HasRoleResolver;

    public function __construct(?User $user)
    {
        $this->userId = $user?->id ?? 0;
    }

    public function validation(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', new IsValidPhone],
            'nip' => ['required', 'numeric', new IsValidDigit(6)],
            'role' => ['required', 'string', 'max:255', Rule::exists('roles', 'name')->whereNull('deleted_at')]
        ];
    }

    public function uniqueBy(): string|array
    {
        return [
            'phone',
            'nip'
        ];
    }

    /**
     * @param array $row
     * @return User|null
     * @throws ValidationException
     */
    public function model(array $row): ?User
    {
        $this->validate($row);
		$this->replace($row);
		return null;
    }

    public function name(): string
    {
        return 'Pegawai';
    }

	public function replace(array $row): void
	{
		User::updateOrCreate([
			'nip' => trim($row['nip']),
		], [
			'role_id' => $this->resolveRoleId($row['role']),
			'name' => Str::title(trim($row['name'])),
			'phone' => trim($row['phone']),
			'password' => Hash::make(trim($row['nip']))
		]);
	}

    public function overwrite(): void
    {
        User::leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->where('users.id', '<>', $this->userId)
            ->whereNull('users.deleted_at')
            ->delete();
    }
}
