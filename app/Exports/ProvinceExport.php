<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping};
use App\Notifications\DataExported;
use App\Services\ProvinceService;

class ProvinceExport implements FromCollection, WithHeadings, WithMapping
{
    private ProvinceService $provinceService;

    public function __construct(ProvinceService $provinceService)
    {
        $this->provinceService = $provinceService;
    }

    public function headings(): array
    {
        return ['Nama Provinsi'];
    }

    public function map($row): array
    {
        return [trim($row->name)];
    }

    public function collection(): Collection
    {
        $data = $this->provinceService->collection();
        if (!is_null(auth()->user())) {
            auth()
                ->user()
                ->notify(new DataExported('Provinsi', $data->count()));
        }
        return $data;
    }
}
