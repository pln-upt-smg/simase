<?php

namespace App\Exports;

use App\Notifications\DataExported;
use App\Services\EmployeeService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping
{
    private EmployeeService $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
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
        $data = $this->employeeService->collection();
        auth()->user()?->notify(new DataExported('Pegawai', $data->count()));
        return $data;
    }
}
