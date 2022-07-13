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
        return ['Area', 'Tipe Area', 'Aset', 'Tipe Aset', 'UoM', 'Kuantitas'];
    }

    public function map($row): array
    {
        return [
            Str::title(trim($row->area->name)),
            Str::title(trim($row->area->areaType->name)),
            Str::title(trim($row->name)),
            Str::title(trim($row->assetType->name)),
            trim($row->assetType->uom),
            $row->quantity,
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
