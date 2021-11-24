<?php

namespace App\Imports;

use App\Imports\Contract\WithDefaultEvents;
use App\Imports\Helper\HasBatchSize;
use App\Imports\Helper\HasChunkSize;
use App\Imports\Helper\HasDefaultEvents;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasImporter;
use App\Models\Area;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class AreasImport implements ToModel, SkipsOnFailure, SkipsOnError, SkipsEmptyRows, WithHeadingRow, WithMultipleSheets, WithChunkReading, WithBatchInserts, WithUpserts, WithEvents, WithDefaultEvents, WithValidation, ShouldQueue, ShouldBeUnique
{
	use Importable, SkipsFailures, SkipsErrors, HasDefaultSheet, HasDefaultEvents, HasImporter, HasChunkSize, HasBatchSize;

	public function __construct(?User $user)
	{
		$this->userId = $user?->id ?? 0;
	}

	public function rules(): array
	{
		return [
			'area' => ['required', 'string', 'max:255'],
			'sloc' => ['required', 'numeric']
		];
	}

	public function uniqueBy(): string|array
	{
		return [
			'area',
			'sloc'
		];
	}

	public function model(array $row): ?Area
	{
		$this->replace($row);
		return null;
	}

	public function name(): string
	{
		return 'Area';
	}

	public function replace(array $row): void
	{
		Area::updateOrCreate([
			'name' => Str::title(trim($row['area'])),
			'sloc' => trim($row['sloc'])
		]);
	}

	public function overwrite(): void
	{
		Area::whereNull('deleted_at')->delete();
	}
}
