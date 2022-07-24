<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping};
use App\Notifications\DataExported;
use App\Services\EmployeeService;

class EmployeeExport implements FromCollection, WithHeadings, WithMapping
{
    private EmployeeService $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function headings(): array
    {
        return ['Nama', 'Telepon', 'NIP', 'Peran', 'Divisi'];
    }

    public function map($row): array
    {
        return [
            trim($row->name),
            trim($row->phone),
            trim($row->nip),
            trim($row->role->name),
            trim($row->division->name),
        ];
    }

    public function collection(): Collection
    {
        $data = $this->employeeService->collection();
        if (!is_null(auth()->user())) {
            auth()
                ->user()
                ->notify(new DataExported('Pegawai', $data->count()));
        }
        return $data;
    }
}
