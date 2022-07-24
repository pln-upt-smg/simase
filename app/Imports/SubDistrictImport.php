<?php

namespace App\Imports;

use Illuminate\Contracts\Queue\{ShouldBeUnique, ShouldQueue};
use Illuminate\Support\Collection;
use App\Imports\Contracts\WithDefaultEvents;
use App\Imports\Helpers\{
    HasBatchSize,
    HasChunkSize,
    HasDefaultEvents,
    HasDefaultSheet,
    HasImporter
};
use App\Models\{SubDistrict, User};
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

class SubDistrictImport implements
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
            'namakecamatan' => ['required', 'string', 'max:255'],
        ];
    }

    public function uniqueBy()
    {
        return ['namakecamatan'];
    }

    public function collection(Collection $collection): void
    {
        foreach ($collection as $row) {
            $this->replace($row->toArray());
        }
    }

    public function name(): string
    {
        return 'Kecamatan';
    }

    public function replace(array $row): void
    {
        if ($this->userId === 0) {
            return;
        }
        SubDistrict::updateOrCreate([
            'created_by' => $this->userId,
            'name' => trim($row['namakecamatan']),
        ]);
    }

    public function overwrite(): void
    {
        SubDistrict::whereNull('deleted_at')->delete();
    }
}
