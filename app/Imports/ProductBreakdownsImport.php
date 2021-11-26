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
use App\Imports\Helper\HasProductMaterialResolver;
use App\Imports\Helper\HasProductResolver;
use App\Models\Period;
use App\Models\ProductBreakdown;
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
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductBreakdownsImport implements ToCollection, SkipsOnFailure, SkipsOnError, SkipsEmptyRows, WithHeadingRow, WithMultipleSheets, WithChunkReading, WithBatchInserts, WithEvents, WithDefaultEvents, WithValidation, ShouldQueue, ShouldBeUnique
{
	use Importable, SkipsFailures, SkipsErrors, HasDefaultSheet, HasDefaultEvents, HasImporter, HasChunkSize, HasBatchSize, HasMultipleSubArea, HasMaterialResolver, HasProductResolver, HasProductMaterialResolver;

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
			'batch' => ['required', 'string', 'max:255'],
			'product' => ['required', 'string', 'max:255', Rule::exists('products', 'code')->where('period_id', $this->periodId)->whereNull('deleted_at')],
			'productdescription' => ['nullable', 'string', 'max:255'],
			'material' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->where('period_id', $this->periodId)->whereNull('deleted_at')],
			'materialdescription' => ['nullable', 'string', 'max:255']
		];
	}

	public function collection(Collection $collection): void
	{
		foreach ($collection as $row) {
			$this->lookupSubArea($row->toArray());
			$this->replace($row->toArray());
		}
	}

	public function name(): string
	{
		return 'FG Material Breakdown';
	}

	public function replace(array $row): void
	{
		$subAreaId = $this->currentSubAreaId;
		$productMaterialId = $this->resolveProductMaterialId($this->resolveProductId($row['product']), $this->resolveMaterialId($row['material']));
		$userId = $this->userId;
		if ($subAreaId === 0 || $productMaterialId === 0 || $userId === 0) {
			return;
		}
		ProductBreakdown::updateOrCreate([
			'sub_area_id' => $subAreaId,
			'product_material_id' => $productMaterialId,
			'user_id' => $userId
		], [
			'batch' => Str::upper(trim($row['batch']))
		]);
	}

	public function overwrite(): void
	{
		ProductBreakdown::leftJoin('product_materials', 'product_materials.id', '=', 'product_breakdowns.product_material_id')
			->leftJoin('materials', 'materials.id', '=', 'product_materials.material_id')
			->where('materials.period_id', $this->periodId)
			->whereNull('product_materials.deleted_at')
			->delete();
	}
}
