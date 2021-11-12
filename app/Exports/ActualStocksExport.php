<?php

namespace App\Exports;

use App\Models\Area;
use App\Models\Period;
use App\Notifications\DataExported;
use App\Services\ActualStockService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ActualStocksExport implements FromCollection, WithHeadings, WithMapping
{
    private ActualStockService $actualStockService;

    private ?Area $area;

    private ?Period $period;

    public function __construct(ActualStockService $actualStockService, ?Area $area, ?Period $period)
    {
        $this->actualStockService = $actualStockService;
        $this->area = $area;
        $this->period = $period;
    }

    public function headings(): array
    {
        return [
            'SubArea',
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
            Str::title(trim($row->subArea->name)),
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
        $data = $this->actualStockService->collection($this->area, $this->period);
        auth()->user()?->notify(new DataExported('Actual Stock', $data->count()));
        return $data;
    }
}
