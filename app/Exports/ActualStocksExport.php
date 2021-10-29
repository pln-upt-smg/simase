<?php

namespace App\Exports;

use App\Models\ActualStock;
use App\Models\Area;
use App\Models\Period;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ActualStocksExport implements FromCollection, WithHeadings, WithMapping
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
            'Batch',
            'MaterialDescription',
            'Quantity',
            'UOM',
            'MTyp'
        ];
    }

    public function map($row): array
    {
        return [
            Str::upper(trim($row->material->code)),
            Str::upper(trim($row->batch)),
            Str::title(trim($row->material->description)),
            (int)$row->quantity,
            Str::upper(trim($row->material->uom)),
            Str::upper(trim($row->material->mtyp))
        ];
    }

    public function collection(): Collection
    {
        $query = ActualStock::whereNull('actual_stocks.deleted_at')
            ->leftJoin('materials', 'materials.id', '=', 'actual_stocks.material_id');
        if (!is_null($this->area)) {
            $query = $query->where('actual_stocks.area_id', $this->area->id);
        }
        if (!is_null($this->period)) {
            $query = $query->where('actual_stocks.period_id', $this->period->id);
        }
        return $query->orderBy('materials.code')->get()->load('material');
    }
}
