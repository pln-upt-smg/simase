<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping};
use App\Notifications\DataExported;
use App\Services\HolderService;

class HolderExport implements FromCollection, WithHeadings, WithMapping
{
    private HolderService $holderService;

    public function __construct(HolderService $holderService)
    {
        $this->holderService = $holderService;
    }

    public function headings(): array
    {
        return ['Nama Pemegang Hak'];
    }

    public function map($row): array
    {
        return [trim($row->name)];
    }

    public function collection(): Collection
    {
        $data = $this->holderService->collection();
        if (!is_null(auth()->user())) {
            auth()
                ->user()
                ->notify(new DataExported('Nama Pemegang Hak', $data->count()));
        }
        return $data;
    }
}
