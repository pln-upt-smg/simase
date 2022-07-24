<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping};
use App\Notifications\DataExported;
use App\Services\AssetSubmissionService;

class AssetSubmissionExport implements FromCollection, WithHeadings, WithMapping
{
    private AssetSubmissionService $assetSubmissionService;

    public function __construct(AssetSubmissionService $assetSubmissionService)
    {
        $this->assetSubmissionService = $assetSubmissionService;
    }

    public function headings(): array
    {
        return [
            'Nama Aset',
            'Tipe Aset',
            'Nama Area',
            'Tipe Area',
            'Kuantitas',
            'Penambahan Kuantitas',
            'UoM',
            'Prioritas',
            'Keterangan',
            'Foto Lampiran',
        ];
    }

    public function map($row): array
    {
        return [
            trim($row->asset->area->name),
            trim($row->asset->area->areaType->name),
            trim($row->asset->name),
            trim($row->asset->assetType->name),
            $row->asset->quantity,
            $row->quantity,
            trim($row->asset->assetType->uom),
            $row->priority,
            trim($row->note),
            $row->assetSubmissionImages->pluck('images')->join(', '),
        ];
    }

    public function collection(): Collection
    {
        $data = $this->assetSubmissionService->collection();
        if (!is_null(auth()->user())) {
            auth()
                ->user()
                ->notify(
                    new DataExported('Laporan Pengajuan Aset', $data->count())
                );
        }
        return $data;
    }
}
