<?php

namespace App\Imports;

use App\Imports\Contract\WithDefaultEvents;
use App\Imports\Helper\HasAreaResolver;
use App\Imports\Helper\HasBatchSize;
use App\Imports\Helper\HasChunkSize;
use App\Imports\Helper\HasDefaultEvents;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasImporter;
use App\Models\SubArea;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
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

class SubAreasImport implements ToCollection, SkipsOnFailure, SkipsOnError, SkipsEmptyRows, WithHeadingRow, WithMultipleSheets, WithChunkReading, WithBatchInserts, WithUpserts, WithEvents, WithDefaultEvents, WithValidation, ShouldQueue, ShouldBeUnique
{
	use Importable, SkipsFailures, SkipsErrors, HasDefaultSheet, HasDefaultEvents, HasImporter, HasChunkSize, HasBatchSize, HasAreaResolver;

	public function __construct(?User $user)
	{
		$this->userId = $user?->id ?? 0;
	}

	public function rules(): array
	{
		return [
			'subarea' => ['required', 'string', 'max:255'],
			'sloc' => ['required', 'numeric', Rule::exists('areas', 'sloc')->whereNull('deleted_at')]
		];
	}

	public function uniqueBy(): string|array
	{
		return [
			'subarea'
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
		return 'Sub Area';
	}

	public function replace(array $row): void
	{
		$areaId = $this->resolveAreaId($row['sloc'], true);
		if ($areaId === 0) {
			return;
		}
		SubArea::updateOrCreate([
			'area_id' => $areaId,
			'name' => Str::title(trim($row['subarea']))
		]);
	}

	public function overwrite(): void
	{
		SubArea::whereNull('deleted_at')->delete();
	}
}
