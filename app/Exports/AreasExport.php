<?php

namespace App\Exports;

use App\Models\Area;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AreasExport implements FromCollection, WithHeadings, WithMapping
{
    public function headings(): array
    {
        return [
            'AreaID',
            'AreaDescription'
        ];
    }

    public function map($row): array
    {
        return [
            (int)$row->id,
            Str::title(trim($row->name))
        ];
    }

    public function collection(): Collection
    {
        return Area::whereNull('deleted_at')->orderBy('id')->get();
    }
}
