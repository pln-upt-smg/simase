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
    HasImporter,
    HasAreaTypeResolver
};
use App\Models\{Area, User};
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

class AreaImport implements
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
        HasBatchSize,
        HasAreaTypeResolver;

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
            'kodearea' => ['required', 'numeric'],
            'namaarea' => ['required', 'string', 'max:255'],
            'tipearea' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'float'],
            'longitude' => ['required', 'float'],
        ];
    }

    public function uniqueBy()
    {
        return ['kodearea'];
    }

    public function collection(Collection $collection): void
    {
        foreach ($collection as $row) {
            $this->replace($row->toArray());
        }
    }

    public function name(): string
    {
        return 'Area';
    }

    public function replace(array $row): void
    {
        $areaTypeId = $this->resolveAreaTypeId($row['tipearea']);
        if ($areaTypeId === 0 || $this->userId === 0) {
            return;
        }
        Area::updateOrCreate([
            'area_type_id' => $areaTypeId,
            'created_by' => $this->userId,
            'code' => trim($row['kodearea']),
            'name' => trim($row['namaarea']),
            'lat' => $row['latitude'],
            'lon' => $row['longitude'],
        ]);
    }

    public function overwrite(): void
    {
        Area::whereNull('deleted_at')->delete();
    }
}
