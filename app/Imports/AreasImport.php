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
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithUpserts;

class AreasImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithMultipleSheets, WithChunkReading, WithBatchInserts, WithUpserts, WithEvents, WithDefaultEvents, ShouldQueue, ShouldBeUnique
{
    use HasDefaultSheet, HasDefaultEvents, HasImporter, HasChunkSize, HasBatchSize;

    public function __construct(?User $user)
    {
        $this->userId = $user?->id ?? 0;
    }

    public function rules(): array
    {
        return [
            'areaid' => ['nullable', 'integer', 'min:1'],
            'areadescription' => ['required', 'max:255']
        ];
    }

    public function uniqueBy(): string|array
    {
        return [
            'areaid',
            'areadescription'
        ];
    }

    public function model(array $row): ?Area
    {
        return new Area([
            'name' => Str::title(trim($row['areadescription']))
        ]);
    }

    public function name(): string
    {
        return 'Area';
    }

    public function overwrite(): void
    {
        Area::whereNull('deleted_at')->delete();
    }
}
