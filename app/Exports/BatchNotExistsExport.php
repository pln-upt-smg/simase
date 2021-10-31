<?php

namespace App\Exports;

use App\Models\Area;
use App\Models\Period;
use App\Services\BatchNotExistService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BatchNotExistsExport implements FromCollection, WithHeadings, WithMapping
{
    private ?Area $area;

    private ?Period $period;

    private BatchNotExistService $batchNotExistService;

    public function __construct(?Area $area, ?Period $period, BatchNotExistService $batchNotExistService)
    {
        $this->area = $area;
        $this->period = $period;
        $this->batchNotExistService = $batchNotExistService;
    }

    public function headings(): array
    {
        return [
            'AreaDescription',
            'Material',
            'MaterialDescription',
            'Batch',
            'Quantity',
            'UOM',
            'BatchStatus',
            'UserID',
            'EntryTime',
            'MTyp'
        ];
    }

    public function map($row): array
    {
        return [
            Str::title(trim($row->area_name)),
            Str::upper(trim($row->material_code)),
            Str::title(trim($row->material_description)),
            Str::upper(trim($row->batch_code)),
            (string)$row->quantity,
            Str::upper(trim($row->uom)),
            (string)$row->batch_status,
            Str::upper(trim($row->creator_nip)),
            (string)$row->creation_date,
            Str::upper(trim($row->mtyp))
        ];
    }

    public function collection(): Collection
    {
        return $this->batchNotExistService->collection($this->area, $this->period);
    }
}
