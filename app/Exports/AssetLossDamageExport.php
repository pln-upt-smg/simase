<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping};
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
            'Nama Aset',
            'Tipe Aset',
            'Nama Area',
            'Tipe Area',
            'Kuantitas',
            'UoM',
            'Prioritas',
            'Keterangan',
            'Foto Lampiran',
        ];
    }

    public function map($row): array
    {
        return [
            trim($row->asset->name),
            trim($row->asset->assetType->name),
            trim($row->asset->area->name),
            trim($row->asset->area->areaType->name),
            $row->quantity,
            trim($row->asset->assetType->uom),
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
