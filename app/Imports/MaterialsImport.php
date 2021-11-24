<?php

namespace App\Imports;

use App\Imports\Contract\WithDefaultEvents;
use App\Imports\Contract\WithQueuedValidation;
use App\Imports\Helper\HasBatchSize;
use App\Imports\Helper\HasChunkSize;
use App\Imports\Helper\HasDefaultEvents;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasImporter;
use App\Imports\Helper\HasValidator;
use App\Models\Material;
use App\Models\Period;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithUpserts;

class MaterialsImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithMultipleSheets, WithChunkReading, WithBatchInserts, WithUpserts, WithEvents, WithDefaultEvents, WithQueuedValidation, ShouldQueue, ShouldBeUnique
{
    use HasDefaultSheet, HasDefaultEvents, HasImporter, HasChunkSize, HasBatchSize, HasValidator;

    private int $periodId;

    public function __construct(?Period $period, ?User $user)
    {
        $this->periodId = $period?->id ?? 0;
        $this->userId = $user?->id ?? 0;
    }

    public function validation(): array
    {
        return [
            'material' => ['required', 'string', 'max:255'],
            'materialdescription' => ['required', 'string', 'max:255'],
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
            'material'
        ];
    }

    /**
     * @param array $row
     * @return Material|null
     * @throws ValidationException
     */
    public function model(array $row): ?Material
    {
        $this->validate($row);
		$this->replace($row);
		return null;
    }

    public function name(): string
    {
        return 'Material Master';
    }

	public function replace(array $row): void
	{
		Material::updateOrCreate([
			'period_id' => $this->periodId,
			'code' => Str::upper(trim($row['material'])),
		], [
			'description' => Str::title(trim($row['materialdescription'])),
			'uom' => Str::upper(trim($row['uom'])),
			'mtyp' => Str::upper(trim($row['mtyp'])),
			'crcy' => Str::upper(trim($row['crcy'])),
			'price' => (int)$row['price'],
			'per' => (int)$row['per']
		]);
	}

    public function overwrite(): void
    {
        Material::where('period_id', $this->periodId)->whereNull('deleted_at')->delete();
    }
}
