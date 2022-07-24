<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping};
use App\Notifications\DataExported;
use App\Services\AreaService;

class AreaExport implements FromCollection, WithHeadings, WithMapping
{
    private AreaService $areaService;

    public function __construct(AreaService $areaService)
    {
        $this->areaService = $areaService;
    }

    public function headings(): array
    {
        return ['Kode Area', 'Nama Area', 'Tipe Area', 'Latitude', 'Longitude'];
    }

    public function map($row): array
    {
        return [
            trim($row->code),
            trim($row->name),
            trim($row->areaType->name),
            $row->lat,
            $row->lon,
        ];
    }

    public function collection(): Collection
    {
        $data = $this->areaService->collection();
        if (!is_null(auth()->user())) {
            auth()
                ->user()
                ->notify(new DataExported('Area', $data->count()));
        }
        return $data;
    }
}
