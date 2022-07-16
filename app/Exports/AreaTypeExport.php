<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Notifications\DataExported;
use App\Services\AreaTypeService;

class AreaTypeExport implements FromCollection, WithHeadings, WithMapping
{
    private AreaTypeService $areaTypeService;

    public function __construct(AreaTypeService $areaTypeService)
    {
        $this->areaTypeService = $areaTypeService;
    }

    public function headings(): array
    {
        return ['Tipe Area'];
    }

    public function map($row): array
    {
        return [Str::title(trim($row->name))];
    }

    public function collection(): Collection
    {
        $data = $this->areaTypeService->collection();
        if (!is_null(auth()->user())) {
            auth()
                ->user()
                ->notify(new DataExported('Tipe Area', $data->count()));
        }
        return $data;
    }
}
