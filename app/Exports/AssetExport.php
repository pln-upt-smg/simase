<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
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
            'Aset',
            'Tipe Aset',
            'UoM',
            'Kuantitas',
            'Kode Area',
            'Area',
            'Tipe Area',
        ];
    }

    public function map($row): array
    {
        return [
            Str::title(trim($row->name)),
            Str::title(trim($row->assetType->name)),
            trim($row->assetType->uom),
            $row->quantity,
            trim($row->area->code),
            Str::title(trim($row->area->name)),
            Str::title(trim($row->area->areaType->name)),
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
