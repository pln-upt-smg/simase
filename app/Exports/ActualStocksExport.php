<?php

namespace App\Exports;

use App\Models\Area;
use App\Models\Period;
use App\Services\ActualStockService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ActualStocksExport implements FromCollection, WithHeadings, WithMapping
{
    private ?Area $area;

    private ?Period $period;

    private ActualStockService $actualStockService;

    public function __construct(?Area $area, ?Period $period, ActualStockService $actualStockService)
    {
        $this->area = $area;
        $this->period = $period;
        $this->actualStockService = $actualStockService;
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
            (string)$row->quantity,
            Str::upper(trim($row->material->uom)),
            Str::upper(trim($row->material->mtyp))
        ];
    }

    public function collection(): Collection
    {
        return $this->actualStockService->collection($this->area, $this->period);
    }
}
