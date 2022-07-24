<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping};
use App\Notifications\DataExported;
use App\Services\AssetService;

class AssetExport implements FromCollection, WithHeadings, WithMapping
{
    private AssetService $assetService;

    public function __construct(AssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    public function headings(): array
    {
        return [
            'Nama Aset',
            'Tipe Aset',
            'Kode Area',
            'Nama Area',
            'Tipe Area',
            'Kuantitas',
            'UoM',
        ];
    }

    public function map($row): array
    {
        return [
            trim($row->name),
            trim($row->assetType->name),
            trim($row->area->code),
            trim($row->area->name),
            trim($row->area->areaType->name),
            $row->quantity,
            trim($row->assetType->uom),
        ];
    }

    public function collection(): Collection
    {
        $data = $this->assetService->collection();
        if (!is_null(auth()->user())) {
            auth()
                ->user()
                ->notify(new DataExported('Aset', $data->count()));
        }
        return $data;
    }
}
