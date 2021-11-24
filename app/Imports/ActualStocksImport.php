<?php

namespace App\Imports;

use App\Imports\Contract\WithDefaultEvents;
use App\Imports\Helper\HasBatchSize;
use App\Imports\Helper\HasChunkSize;
use App\Imports\Helper\HasDefaultEvents;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasImporter;
use App\Imports\Helper\HasMaterialResolver;
use App\Imports\Helper\HasMultipleSubArea;
use App\Models\ActualStock;
use App\Models\Period;
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

class ActualStocksImport implements ToCollection, SkipsOnFailure, SkipsOnError, SkipsEmptyRows, WithHeadingRow, WithMultipleSheets, WithChunkReading, WithBatchInserts, WithUpserts, WithEvents, WithDefaultEvents, WithValidation, ShouldQueue, ShouldBeUnique
{
	use Importable, SkipsFailures, SkipsErrors, HasDefaultSheet, HasDefaultEvents, HasImporter, HasChunkSize, HasBatchSize, HasMultipleSubArea, HasMaterialResolver;

	private int $periodId;

	public function __construct(?Period $period, ?User $user)
	{
		$this->periodId = $period?->id ?? 0;
		$this->userId = $user?->id ?? 0;
	}

	public function rules(): array
	{
		return [
			'subarea' => ['required', 'string', 'max:255', Rule::exists('sub_areas', 'name')->whereNull('deleted_at')],
			'material' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->where('period_id', $this->periodId)->whereNull('deleted_at')],
			'batch' => ['required', 'string', 'max:255'],
			'materialdescription' => ['nullable', 'string', 'max:255'],
			'quantity' => ['required', 'numeric', 'min:0'],
			'uom' => ['nullable', 'string', 'max:255'],
			'mtyp' => ['nullable', 'string', 'max:255']
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
			$this->lookupSubArea($row);
			$this->replace($row->toArray());
		}
	}

	public function name(): string
	{
		return 'Actual Stock';
	}

	public function replace(array $row): void
	{
		ActualStock::updateOrCreate([
			'sub_area_id' => $this->currentSubAreaId,
			'material_id' => $this->resolveMaterialId($row['material']),
			'user_id' => $this->userId
		], [
			'batch' => Str::upper(trim($row['batch'])),
			'quantity' => $row['quantity']
		]);
	}

	public function overwrite(): void
	{
		ActualStock::leftJoin('materials', 'materials.id', '=', 'actual_stocks.material_id')
			->where('materials.period_id', $this->periodId)
			->whereNull('actual_stocks.deleted_at')
			->delete();
	}
}
