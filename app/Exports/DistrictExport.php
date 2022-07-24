<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping};
use App\Notifications\DataExported;
use App\Services\DistrictService;

class DistrictExport implements FromCollection, WithHeadings, WithMapping
{
    private DistrictService $districtService;

    public function __construct(DistrictService $districtService)
    {
        $this->districtService = $districtService;
    }

    public function headings(): array
    {
        return ['Nama Kabupaten / Kotamadya'];
    }

    public function map($row): array
    {
        return [trim($row->name)];
    }

    public function collection(): Collection
    {
        $data = $this->districtService->collection();
        if (!is_null(auth()->user())) {
            auth()
                ->user()
                ->notify(
                    new DataExported('Kabupaten / Kotamadya', $data->count())
                );
        }
        return $data;
    }
}
