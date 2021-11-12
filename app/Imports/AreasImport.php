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
use App\Models\Area;
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

class AreasImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithMultipleSheets, WithChunkReading, WithBatchInserts, WithUpserts, WithEvents, WithDefaultEvents, WithQueuedValidation, ShouldQueue, ShouldBeUnique
{
    use HasDefaultSheet, HasDefaultEvents, HasImporter, HasChunkSize, HasBatchSize, HasValidator;

    public function __construct(?User $user)
    {
        $this->userId = $user?->id ?? 0;
    }

    public function validation(): array
    {
        return [
            'area' => ['required', 'string', 'max:255'],
            'sloc' => ['required', 'numeric']
        ];
    }

    public function uniqueBy(): string|array
    {
        return [
            'area',
            'sloc'
        ];
    }

    /**
     * @param array $row
     * @return Area|null
     * @throws ValidationException
     */
    public function model(array $row): ?Area
    {
        $this->validate($row);
        return new Area([
            'name' => Str::title(trim($row['area'])),
            'sloc' => trim($row['sloc'])
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
