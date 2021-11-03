<?php

namespace App\Exports;

use App\Models\Area;
use App\Models\Period;
use App\Notifications\DataExported;
use App\Services\PidService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PidExport implements FromCollection, WithHeadings, WithMapping
{
    private ?Area $area;

    private ?Period $period;

    private PidService $pidService;

    public function __construct(?Area $area, ?Period $period, PidService $pidService)
    {
        $this->area = $area;
        $this->period = $period;
        $this->pidService = $pidService;
    }

    public function headings(): array
    {
        return [
            'Material',
            'MaterialDescription',
            'UOM',
            'MTyp',
            'Batch',
            'Unrestricted',
            'QuanlInsp',
            'BookQty',
            'StockTakingQty',
            'GapQty'
        ];
    }

    public function map($row): array
    {
        return [
            Str::upper(trim($row->material_code)),
            Str::title(trim($row->material_description)),
            Str::upper(trim($row->uom)),
            Str::upper(trim($row->mtyp)),
            Str::upper(trim($row->batch_code)),
            (string)$row->unrestricted,
            (string)$row->qualinsp,
            (string)$row->book_qty,
            (string)$row->actual_qty,
            (string)$row->gap_qty
        ];
    }

    public function collection(): Collection
    {
        $data = $this->pidService->collection($this->area, $this->period);
        auth()->user()?->notify(new DataExported('PID', $data->count()));
        return $data;
    }
}
