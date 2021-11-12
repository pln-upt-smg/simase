<?php

namespace App\Exports;

use App\Notifications\DataExported;
use App\Services\AreaService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AreasExport implements FromCollection, WithHeadings, WithMapping
{
    private AreaService $areaService;

    public function __construct(AreaService $areaService)
    {
        $this->areaService = $areaService;
    }

    public function headings(): array
    {
        return [
            'Area',
            'SLoc'
        ];
    }

    public function map($row): array
    {
        return [
            Str::title(trim($row->name)),
            trim($row->sloc)
        ];
    }

    public function collection(): Collection
    {
        $data = $this->areaService->collection();
        auth()->user()?->notify(new DataExported('Area', $data->count()));
        return $data;
    }
}
