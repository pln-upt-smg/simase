<?php

namespace App\Imports;

use App\Imports\Contracts\WithDefaultEvents;
use App\Imports\Helpers\{
    HasBatchSize,
    HasChunkSize,
    HasDefaultEvents,
    HasDefaultSheet,
    HasImporter
};
use App\Models\{AreaType, User};
use Illuminate\Contracts\Queue\{ShouldBeUnique, ShouldQueue};
use Illuminate\Support\{Collection, Str};
use Maatwebsite\Excel\Concerns\{
    Importable,
    SkipsEmptyRows,
    SkipsErrors,
    SkipsFailures,
    SkipsOnError,
    SkipsOnFailure,
    ToCollection,
    WithBatchInserts,
    WithChunkReading,
    WithEvents,
    WithHeadingRow,
    WithMultipleSheets,
    WithUpserts,
    WithValidation
};

class AreaTypeImport implements
    ToCollection,
    SkipsOnFailure,
    SkipsOnError,
    SkipsEmptyRows,
    WithHeadingRow,
    WithMultipleSheets,
    WithChunkReading,
    WithBatchInserts,
    WithUpserts,
    WithEvents,
    WithDefaultEvents,
    WithValidation,
    ShouldQueue,
    ShouldBeUnique
{
    use Importable,
        SkipsFailures,
        SkipsErrors,
        HasDefaultSheet,
        HasDefaultEvents,
        HasImporter,
        HasChunkSize,
        HasBatchSize;

    public function __construct(?User $user)
    {
        if (is_null($user)) {
            $this->userId = 0;
        } else {
            $this->userId = $user->id;
        }
    }

    public function rules(): array
    {
        return [
            'tipearea' => ['required', 'string', 'max:255'],
        ];
    }

    public function uniqueBy()
    {
        return ['tipearea'];
    }

    public function collection(Collection $collection): void
    {
        foreach ($collection as $row) {
            $this->replace($row->toArray());
        }
    }

    public function name(): string
    {
        return 'Tipe Area';
    }

    public function replace(array $row): void
    {
        if ($this->userId === 0) {
            return;
        }
        AreaType::updateOrCreate([
            'created_by' => $this->userId,
            'name' => Str::title(trim($row['tipearea'])),
        ]);
    }

    public function overwrite(): void
    {
        AreaType::whereNull('deleted_at')->delete();
    }
}
