<?php

namespace App\Exports;

use App\Models\Area;
use App\Models\BookStock;
use App\Models\Period;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BookStocksExport implements FromCollection, WithHeadings, WithMapping
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
            'Plnt',
            'SLoc',
            'Batch',
            'Unrestricted',
            'QualInsp',
            'MaterialDescription',
            'UOM',
            'MTyp'
        ];
    }

    public function map($row): array
    {
        return [
            Str::upper(trim($row->material->code)),
            $row->plnt,
            $row->sloc,
            $row->batch,
            $row->unrestricted,
            $row->qualinsp,
            Str::title(trim($row->material->description)),
            Str::upper(trim($row->material->uom)),
            Str::upper(trim($row->material->mtyp))
        ];
    }

    public function collection(): Collection
    {
        $query = BookStock::whereNull('deleted_at')
            ->leftJoin('materials', 'materials.id', '=', 'actual_stocks.material_id');
        if (!is_null($this->area)) {
            $query = $query->where('materials.area_id', $this->area->id);
        }
        if (!is_null($this->period)) {
            $query = $query->where('materials.period_id', $this->period->id);
        }
        return $query->orderBy('materials.code')->get();
    }
}
