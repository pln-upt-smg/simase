<?php

namespace App\Exports;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OperatorsExport implements FromCollection, WithHeadings, WithMapping
{
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
            $row->role->name
        ];
    }

    public function collection(): Collection
    {
        return User::whereRoleId(Role::operator()?->id ?? 2)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get()
            ->load('role');
    }
}
