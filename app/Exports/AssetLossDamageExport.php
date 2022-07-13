<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Notifications\DataExported;
use App\Services\AssetSubmissionService;

class AssetExport implements FromCollection, WithHeadings, WithMapping
{
    private AssetSubmissionService $assetSubmissionService;

    public function __construct(AssetSubmissionService $assetSubmissionService)
    {
        $this->assetSubmissionService = $assetSubmissionService;
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
        $data = $this->assetSubmissionService->collection();
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
