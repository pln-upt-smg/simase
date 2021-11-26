<?php

namespace App\Imports;

use App\Imports\Contract\WithDefaultEvents;
use App\Imports\Helper\HasAreaResolver;
use App\Imports\Helper\HasBatchSize;
use App\Imports\Helper\HasChunkSize;
use App\Imports\Helper\HasDefaultEvents;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasImporter;
use App\Imports\Helper\HasMaterialResolver;
use App\Models\Batch;
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

class BatchesImport implements ToCollection, SkipsOnFailure, SkipsOnError, SkipsEmptyRows, WithHeadingRow, WithMultipleSheets, WithChunkReading, WithBatchInserts, WithUpserts, WithEvents, WithDefaultEvents, WithValidation, ShouldQueue, ShouldBeUnique
{
	use Importable, SkipsFailures, SkipsErrors, HasDefaultSheet, HasDefaultEvents, HasImporter, HasChunkSize, HasBatchSize, HasAreaResolver, HasMaterialResolver;

	public function __construct(?User $user)
	{
		$this->userId = $user?->id ?? 0;
	}

	public function rules(): array
	{
		return [
			'batch' => ['required', 'string', 'max:255'],
			'material' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->whereNull('deleted_at')],
			'sloc' => ['required', 'numeric', Rule::exists('areas', 'sloc')->whereNull('deleted_at')]
		];
	}

	public function uniqueBy(): string|array
	{
		return [
			'material'
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
		return 'Batch';
	}

	public function replace(array $row): void
	{
		$areaId = $this->resolveAreaId($row['sloc'], true);
		$materialId = $this->resolveMaterialId($row['material']);
		if ($areaId === 0 || $materialId === 0) {
			return;
		}
		Batch::updateOrCreate([
			'area_id' => $areaId,
			'material_id' => $materialId,
			'code' => Str::upper(trim($row['batch']))
		]);
	}

	public function overwrite(): void
	{
		Batch::whereNull('deleted_at')->delete();
	}
}
