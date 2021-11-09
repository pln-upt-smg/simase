<?php

namespace App\Imports;

use App\Imports\Contract\WithDefaultEvents;
use App\Imports\Helper\HasBatchSize;
use App\Imports\Helper\HasChunkSize;
use App\Imports\Helper\HasDefaultEvents;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasImporter;
use App\Models\Material;
use App\Models\Period;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithUpserts;

class MaterialsImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithMultipleSheets, WithChunkReading, WithBatchInserts, WithUpserts, WithEvents, WithDefaultEvents, ShouldQueue, ShouldBeUnique
{
    use HasDefaultSheet, HasDefaultEvents, HasImporter, HasChunkSize, HasBatchSize;

    private int $periodId;

    public function __construct(?Period $period, ?User $user)
    {
        $this->periodId = $period?->id ?? 0;
        $this->userId = $user?->id ?? 0;
    }

    public function rules(): array
    {
        return [
            'material' => ['required', 'max:255'],
            'materialdescription' => ['required', 'max:255'],
            'uom' => ['required', 'max:255'],
            'mtyp' => ['required', 'max:255'],
            'crcy' => ['required', 'max:255'],
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

    public function model(array $row): ?Material
    {
        return new Material([
            'period_id' => $this->periodId,
            'code' => Str::upper(trim($row['material'])),
            'description' => Str::title(trim($row['materialdescription'])),
            'uom' => Str::upper(trim($row['uom'])),
            'mtyp' => Str::upper(trim($row['mtyp'])),
            'crcy' => Str::upper(trim($row['crcy'])),
            'price' => (int)$row['price'],
            'per' => (int)$row['per']
        ]);
    }

    public function name(): string
    {
        return 'Material Master';
    }

    public function overwrite(): void
    {
        Material::where('period_id', $this->periodId)->whereNull('deleted_at')->delete();
    }
}
