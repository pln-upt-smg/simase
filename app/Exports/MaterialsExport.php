<?php

namespace App\Exports;

use App\Models\Material;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MaterialsExport implements FromCollection, WithHeadings, WithMapping
{
    public function headings(): array
    {
        return [
            'Material',
            'MaterialDescription',
            'UOM',
            'MTyp',
            'Crcy',
            'Price',
            'Per',
            'LastChange',
            'Area',
            'Period'
        ];
    }

    public function map($row): array
    {
        return [
            Str::upper(trim($row->code)),
            Str::title(trim($row->description)),
            Str::upper(trim($row->uom)),
            Str::upper(trim($row->mtyp)),
            Str::upper(trim($row->crcy)),
            $row->price,
            $row->per,
            $row->updated_at->format('d-M-y'),
            $row->area->name,
            $row->period->name
        ];
    }

    public function collection(): Collection
    {
        return Material::whereNull('deleted_at')
            ->orderBy('code')
            ->get()
            ->load(['area', 'period']);
    }
}
