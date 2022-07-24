<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping};
use App\Notifications\DataExported;
use App\Services\AssetTypeService;

class AssetTypeExport implements FromCollection, WithHeadings, WithMapping
{
    private AssetTypeService $assetTypeService;

    public function __construct(AssetTypeService $assetTypeService)
    {
        $this->assetTypeService = $assetTypeService;
    }

    public function headings(): array
    {
        return ['Nama Tipe Aset'];
    }

    public function map($row): array
    {
        return [trim($row->name)];
    }

    public function collection(): Collection
    {
        $data = $this->assetTypeService->collection();
        if (!is_null(auth()->user())) {
            auth()
                ->user()
                ->notify(new DataExported('Tipe Aset', $data->count()));
        }
        return $data;
    }
}
