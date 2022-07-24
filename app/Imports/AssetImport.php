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
    HasAreaResolver
};
use App\Models\{Asset, User};
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

class AssetImport implements
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
        HasAreaResolver;

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
            'namaaset' => ['required', 'string', 'max:255'],
            'tipeaset' => ['required', 'string', 'max:255'],
            'uom' => ['required', 'string', 'max:255'],
            'kuantitas' => ['required', 'numeric', 'min:0'],
            'kodearea' => ['required', 'string', 'max:255'],
            'area' => ['required', 'string', 'max:255'],
            'tipearea' => ['required', 'string', 'max:255'],
        ];
    }

    public function uniqueBy()
    {
        return ['namaaset'];
    }

    public function collection(Collection $collection): void
    {
        foreach ($collection as $row) {
            $this->replace($row->toArray());
        }
    }

    public function name(): string
    {
        return 'Aset';
    }

    public function replace(array $row): void
    {
        $assetTypeId = $this->resolveAssetType($row['tipeaset']);
        $areaId = $this->resolveAreaId($row['kodearea'], true);
        if ($areaId === 0 || $this->userId === 0) {
            return;
        }
        Asset::updateOrCreate([
            'asset_type_id' => $assetTypeId,
            'area_id' => $areaId,
            'created_by' => $this->userId,
            'name' => trim($row['namaaset']),
            'quantity' => $row['kuantitas'],
        ]);
    }

    public function overwrite(): void
    {
        Asset::whereNull('deleted_at')->delete();
    }
}
