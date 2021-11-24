<?php

namespace App\Imports;

use App\Imports\Contract\WithDefaultEvents;
use App\Imports\Helper\HasBatchSize;
use App\Imports\Helper\HasChunkSize;
use App\Imports\Helper\HasDefaultEvents;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasImporter;
use App\Models\Period;
use App\Models\Product;
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

class ProductsImport implements ToModel, SkipsOnFailure, SkipsOnError, SkipsEmptyRows, WithHeadingRow, WithMultipleSheets, WithChunkReading, WithBatchInserts, WithUpserts, WithEvents, WithDefaultEvents, WithValidation, ShouldQueue, ShouldBeUnique
{
	use Importable, SkipsFailures, SkipsErrors, HasDefaultSheet, HasDefaultEvents, HasImporter, HasChunkSize, HasBatchSize;

	private int $periodId;

	public function __construct(?Period $period, ?User $user)
	{
		$this->periodId = $period?->id ?? 0;
		$this->userId = $user?->id ?? 0;
	}

	public function rules(): array
	{
		return [
			'product' => ['required', 'string', 'max:255'],
			'productdescription' => ['nullable', 'string', 'max:255'],
			'uom' => ['required', 'string', 'max:255'],
			'mtyp' => ['required', 'string', 'max:255'],
			'crcy' => ['required', 'string', 'max:255'],
			'price' => ['required', 'integer', 'min:0'],
			'per' => ['required', 'integer', 'min:0']
		];
	}

	public function uniqueBy(): string|array
	{
		return [
			'product'
		];
	}

	public function model(array $row): ?Product
	{
		$this->replace($row);
		return null;
	}

	public function name(): string
	{
		return 'FG Master';
	}

	public function replace(array $row): void
	{
		Product::updateOrCreate([
			'period_id' => $this->periodId,
			'code' => Str::upper(trim($row['product']))
		], [
			'description' => Str::title(trim($row['productdescription'])),
			'uom' => Str::upper(trim($row['uom'])),
			'mtyp' => Str::upper(trim($row['mtyp'])),
			'crcy' => Str::upper(trim($row['crcy'])),
			'price' => (int)$row['price'],
			'per' => (int)$row['per']
		]);
	}

	public function overwrite(): void
	{
		Product::where('period_id', $this->periodId)->whereNull('deleted_at')->delete();
	}
}
