<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping};
use App\Notifications\DataExported;
use App\Services\UrbanVillageService;

class UrbanVillageExport implements FromCollection, WithHeadings, WithMapping
{
    private UrbanVillageService $urbanVillageService;

    public function __construct(UrbanVillageService $urbanVillageService)
    {
        $this->urbanVillageService = $urbanVillageService;
    }

    public function headings(): array
    {
        return ['Nama Kelurahan'];
    }

    public function map($row): array
    {
        return [trim($row->name)];
    }

    public function collection(): Collection
    {
        $data = $this->urbanVillageService->collection();
        if (!is_null(auth()->user())) {
            auth()
                ->user()
                ->notify(new DataExported('Kelurahan', $data->count()));
        }
        return $data;
    }
}
