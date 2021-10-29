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
            'LastChange'
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
            (string)$row->price,
            (string)$row->per,
            (string)$row->updated_at->format('d-M-y')
        ];
    }

    public function collection(): Collection
    {
        $query = Material::whereNull('deleted_at');
        if (!is_null($this->area)) {
            $query = $query->where('area_id', $this->area->id);
        }
        if (!is_null($this->period)) {
            $query = $query->where('period_id', $this->period->id);
        }
        return $query->orderBy('code')->get()->load(['area', 'period']);
    }
}
