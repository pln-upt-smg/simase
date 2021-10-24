<?php

namespace App\Exports;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OperatorsExport implements FromCollection, WithHeadings, WithMapping
{
    public function headings(): array
    {
        return [
            'Nama',
            'Nomor Telepon',
            'NIP',
            'Peran'
        ];
    }

    public function map($row): array
    {
        return [
            $row->name,
            $row->phone,
            $row->nip,
            $row->role->name
        ];
    }

    public function collection(): Collection
    {
        return User::whereRoleId(Role::operator()?->id ?? 2)->orderBy('name')->get()->load('role');
    }
}
