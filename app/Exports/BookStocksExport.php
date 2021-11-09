<?php

namespace App\Exports;

use App\Models\Area;
use App\Models\Period;
use App\Notifications\DataExported;
use App\Services\BookStockService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BookStocksExport implements FromCollection, WithHeadings, WithMapping
{
    private ?Area $area;

    private ?Period $period;

    private BookStockService $bookStockService;

    public function __construct(?Area $area, ?Period $period, BookStockService $bookStockService)
    {
        $this->area = $area;
        $this->period = $period;
        $this->bookStockService = $bookStockService;
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
            'Quantity',
            'UOM',
            'MTyp'
        ];
    }

    public function map($row): array
    {
        return [
            Str::upper(trim($row->material->code)),
            (string)$row->plnt,
            trim($row->area->sloc),
            Str::upper(trim($row->batch)),
            (string)$row->unrestricted,
            (string)$row->qualinsp,
            Str::title(trim($row->material->description)),
            (string)$row->quantity,
            Str::upper(trim($row->material->uom)),
            Str::upper(trim($row->material->mtyp))
        ];
    }

    public function collection(): Collection
    {
        $data = $this->bookStockService->collection($this->area, $this->period);
        auth()->user()?->notify(new DataExported('Book Stock', $data->count()));
        return $data;
    }
}
