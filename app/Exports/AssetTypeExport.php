<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
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
        return ['Tipe Aset', 'UoM'];
    }

    public function map($row): array
    {
        return [Str::title(trim($row->name)), trim($row->uom)];
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
