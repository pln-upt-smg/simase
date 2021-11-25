<?php

namespace App\Imports;

use App\Imports\Contract\WithDefaultEvents;
use App\Imports\Helper\HasBatchSize;
use App\Imports\Helper\HasChunkSize;
use App\Imports\Helper\HasDefaultEvents;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasImporter;
use App\Imports\Helper\HasMaterialResolver;
use App\Imports\Helper\HasMultipleArea;
use App\Models\BookStock;
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

class BookStocksImport implements ToCollection, SkipsOnFailure, SkipsOnError, SkipsEmptyRows, WithHeadingRow, WithMultipleSheets, WithChunkReading, WithBatchInserts, WithUpserts, WithEvents, WithDefaultEvents, WithValidation, ShouldQueue, ShouldBeUnique
{
	use Importable, SkipsFailures, SkipsErrors, HasDefaultSheet, HasDefaultEvents, HasImporter, HasChunkSize, HasBatchSize, HasMultipleArea, HasMaterialResolver;

	private int $periodId;

	public function __construct(?Period $period, ?User $user)
	{
		$this->periodId = $period?->id ?? 0;
		$this->userId = $user?->id ?? 0;
	}

	public function rules(): array
	{
		return [
			'material' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->where('period_id', $this->periodId)->whereNull('deleted_at')],
			'plnt' => ['required', 'integer', 'min:0'],
			'sloc' => ['required', 'string', 'max:255', Rule::exists('areas', 'sloc')->whereNull('deleted_at')],
			'batch' => ['required', 'string', 'max:255'],
			'unrestricted' => ['required', 'numeric'],
			'qualinsp' => ['required', 'integer', 'min:0'],
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
			$this->lookupArea($row, true);
			$this->replace($row->toArray());
		}
	}

	public function name(): string
	{
		return 'Book Stock';
	}

	public function replace(array $row): void
	{
		$areaId = $this->currentAreaId;
		$materialId = $this->resolveMaterialId($row['material']);
		if ($areaId === 0 || $materialId === 0) {
			return;
		}
		BookStock::updateOrCreate([
			'area_id' => $areaId,
			'material_id' => $materialId,
		], [
			'batch' => Str::upper(trim($row['batch'])),
			'quantity' => $row['quantity'],
			'plnt' => (int)$row['plnt'],
			'unrestricted' => $row['unrestricted'],
			'qualinsp' => (int)$row['qualinsp']
		]);
	}

	public function overwrite(): void
	{
		BookStock::leftJoin('materials', 'materials.id', '=', 'book_stocks.material_id')
			->where('materials.period_id', $this->periodId)
			->whereNull('book_stocks.deleted_at')
			->delete();
	}
}
