<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping};
use App\Notifications\DataExported;
use App\Services\SubDistrictService;

class SubDistrictExport implements FromCollection, WithHeadings, WithMapping
{
    private SubDistrictService $subDistrictService;

    public function __construct(SubDistrictService $subDistrictService)
    {
        $this->subDistrictService = $subDistrictService;
    }

    public function headings(): array
    {
        return ['Nama Kecamatan'];
    }

    public function map($row): array
    {
        return [trim($row->name)];
    }

    public function collection(): Collection
    {
        $data = $this->subDistrictService->collection();
        if (!is_null(auth()->user())) {
            auth()
                ->user()
                ->notify(new DataExported('Kecamatan', $data->count()));
        }
        return $data;
    }
}
