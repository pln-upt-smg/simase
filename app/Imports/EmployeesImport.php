<?php

namespace App\Imports;

use App\Imports\Contract\WithDefaultEvents;
use App\Imports\Helper\HasBatchSize;
use App\Imports\Helper\HasChunkSize;
use App\Imports\Helper\HasDefaultEvents;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasImporter;
use App\Imports\Helper\HasRoleResolver;
use App\Models\User;
use App\Rules\IsValidDigit;
use App\Rules\IsValidPhone;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class EmployeesImport implements ToCollection, SkipsOnFailure, SkipsOnError, SkipsEmptyRows, WithHeadingRow, WithMultipleSheets, WithChunkReading, WithBatchInserts, WithUpserts, WithEvents, WithDefaultEvents, WithValidation, ShouldQueue, ShouldBeUnique
{
	use Importable, SkipsFailures, SkipsErrors, HasDefaultSheet, HasDefaultEvents, HasImporter, HasChunkSize, HasBatchSize, HasRoleResolver;

	public function __construct(?User $user)
	{
		$this->userId = $user?->id ?? 0;
	}

	public function rules(): array
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
		User::updateOrCreate([
			'nip' => trim($row['nip']),
		], [
			'role_id' => $roleId,
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
