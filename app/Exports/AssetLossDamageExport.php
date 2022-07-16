<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Notifications\DataExported;
use App\Services\AssetLossDamageService;

class AssetLossDamageExport implements FromCollection, WithHeadings, WithMapping
{
    private AssetLossDamageService $assetLossDamageService;

    public function __construct(AssetLossDamageService $assetLossDamageService)
    {
        $this->assetLossDamageService = $assetLossDamageService;
    }

    public function headings(): array
    {
        return [
            'Area',
            'Tipe Area',
            'Aset',
            'Tipe Aset',
            'UoM',
            'Kuantitas',
            'Penambahan Kuantitas',
            'Prioritas',
            'Keterangan',
            'Foto Lampiran',
        ];
    }

    public function map($row): array
    {
        return [
            Str::title(trim($row->asset->area->name)),
            Str::title(trim($row->asset->area->areaType->name)),
            Str::title(trim($row->asset->name)),
            Str::title(trim($row->asset->assetType->name)),
            trim($row->asset->assetType->uom),
            $row->asset->quantity,
            $row->quantity,
            $row->priority,
            trim($row->note),
            $row->assetLossDamageImages->pluck('images')->join(', '),
        ];
    }

    public function collection(): Collection
    {
        $data = $this->assetLossDamageService->collection();
        if (!is_null(auth()->user())) {
            auth()
                ->user()
                ->notify(
                    new DataExported('Laporan Kehilangan Aset', $data->count())
                );
        }
        return $data;
    }
}
