<?php

namespace App\Exports;

use App\Models\Area;
use App\Models\Material;
use App\Models\Period;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MaterialsExport implements FromCollection, WithHeadings, WithMapping
{
    private ?Area $area;

    private ?Period $period;

    public function __construct(?Area $area, ?Period $period)
    {
        $this->area = $area;
        $this->period = $period;
    }

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
            ->where('area_id', $this->area?->id ?? 0)
            ->where('period_id', $this->period?->id ?? 0)
            ->orderBy('code')
            ->get()
            ->load(['area', 'period']);
    }
}
