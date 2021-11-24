<?php

namespace App\Imports;

use App\Imports\Contract\WithDefaultEvents;
use App\Imports\Contract\WithQueuedValidation;
use App\Imports\Helper\HasAreaResolver;
use App\Imports\Helper\HasBatchSize;
use App\Imports\Helper\HasChunkSize;
use App\Imports\Helper\HasDefaultEvents;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasImporter;
use App\Imports\Helper\HasValidator;
use App\Models\SubArea;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
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

class SubAreasImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithMultipleSheets, WithChunkReading, WithBatchInserts, WithUpserts, WithEvents, WithDefaultEvents, WithQueuedValidation, ShouldQueue, ShouldBeUnique
{
	use HasDefaultSheet, HasDefaultEvents, HasImporter, HasChunkSize, HasBatchSize, HasValidator, HasAreaResolver;

	public function __construct(?User $user)
	{
		$this->userId = $user?->id ?? 0;
	}

	public function validation(): array
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

	/**
	 * @param array $row
	 * @return SubArea|null
	 * @throws ValidationException
	 */
	public function model(array $row): ?SubArea
	{
		$this->validate($row);
		$this->replace($row);
		return null;
	}

	public function name(): string
	{
		return 'Sub Area';
	}

	public function replace(array $row): void
	{
		SubArea::updateOrCreate([
			'area_id' => $this->resolveAreaId($row['sloc'], true),
			'name' => Str::title(trim($row['subarea']))
		]);
	}

	public function overwrite(): void
	{
		SubArea::whereNull('deleted_at')->delete();
	}
}
