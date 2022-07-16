<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
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
        return ['Kode', 'Area', 'Tipe Area', 'Latitude', 'Longitude'];
    }

    public function map($row): array
    {
        return [
            trim($row->code),
            Str::title(trim($row->name)),
            Str::title(trim($row->areaType->name)),
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
