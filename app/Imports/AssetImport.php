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
            'techidentno' => ['required', 'string', 'max:255'],
            'namaaset' => ['required', 'string', 'max:255'],
            'tipeaset' => ['required', 'string', 'max:255'],
            'kuantitas' => ['required', 'numeric', 'min:0'],
            'funcloc' => ['required', 'string', 'max:255'],
        ];
    }

    public function uniqueBy()
    {
        return ['techidentno'];
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
        $areaId = $this->resolveAreaId($row['funcloc'], true);
        if ($areaId === 0 || $this->userId === 0) {
            return;
        }
        Asset::updateOrCreate([
            'asset_type_id' => $assetTypeId,
            'area_id' => $areaId,
            'created_by' => $this->userId,
            'techidentno' => trim($row['techidentno']),
            'name' => trim($row['namaaset']),
            'quantity' => $row['kuantitas'],
        ]);
    }

    public function overwrite(): void
    {
        Asset::leftJoin('users', 'users.id', '=', 'assets.created_by')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->whereNull('deleted_at')
            ->delete();
    }
}
