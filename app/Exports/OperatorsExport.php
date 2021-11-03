<?php

namespace App\Exports;

use App\Notifications\DataExported;
use App\Services\OperatorService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OperatorsExport implements FromCollection, WithHeadings, WithMapping
{
    private OperatorService $operatorService;

    public function __construct(OperatorService $operatorService)
    {
        $this->operatorService = $operatorService;
    }

    public function headings(): array
    {
        return [
            'Name',
            'Phone',
            'NIP',
            'Role'
        ];
    }

    public function map($row): array
    {
        return [
            Str::title(trim($row->name)),
            trim($row->phone),
            trim($row->nip),
            Str::title(trim($row->role->name))
        ];
    }

    public function collection(): Collection
    {
        $data = $this->operatorService->collection();
        auth()->user()?->notify(new DataExported('Pegawai', $data->count()));
        return $data;
    }
}
