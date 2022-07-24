<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping};
use App\Notifications\DataExported;
use App\Services\AssetTransferService;

class AssetTransferExport implements FromCollection, WithHeadings, WithMapping
{
    private AssetTransferService $assetTransferService;

    public function __construct(AssetTransferService $assetTransferService)
    {
        $this->assetTransferService = $assetTransferService;
    }

    public function headings(): array
    {
        return [
            'Nama Aset',
            'Tipe Aset',
            'Area Asal',
            'Tipe Area Asal',
            'Area Tujuan',
            'Tipe Area Tujuan',
            'Kuantitas',
            'Pemindahan Kuantitas',
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
            trim($row->area->name),
            trim($row->area->areaType->name),
            $row->asset->quantity,
            $row->quantity,
            trim($row->asset->assetType->uom),
            $row->priority,
            trim($row->note),
            $row->assetTransferImages->pluck('images')->join(', '),
        ];
    }

    public function collection(): Collection
    {
        $data = $this->assetTransferService->collection();
        if (!is_null(auth()->user())) {
            auth()
                ->user()
                ->notify(
                    new DataExported('Laporan Transfer Aset', $data->count())
                );
        }
        return $data;
    }
}
