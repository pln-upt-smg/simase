<?php

namespace App\Exports;

use App\Notifications\DataExported;
use App\Services\SubAreaService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SubAreasExport implements FromCollection, WithHeadings, WithMapping
{
    private SubAreaService $subAreaService;

    public function __construct(SubAreaService $subAreaService)
    {
        $this->subAreaService = $subAreaService;
    }

    public function headings(): array
    {
        return [
            'SubArea',
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
        $data = $this->subAreaService->collection();
        auth()->user()?->notify(new DataExported('Sub Area', $data->count()));
        return $data;
    }
}
