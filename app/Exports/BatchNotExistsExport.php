<?php

namespace App\Exports;

use App\Models\Area;
use App\Models\Period;
use App\Notifications\DataExported;
use App\Services\BatchNotExistService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BatchNotExistsExport implements FromCollection, WithHeadings, WithMapping
{
    private BatchNotExistService $batchNotExistService;

    private ?Area $area;

    private ?Period $period;

    public function __construct(BatchNotExistService $batchNotExistService, ?Area $area, ?Period $period)
    {
        $this->batchNotExistService = $batchNotExistService;
        $this->area = $area;
        $this->period = $period;
    }

    public function headings(): array
    {
        return [
            'SubArea',
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
            Str::title(trim($row->subArea->name)),
            Str::upper(trim($row->material->code)),
            Str::title(trim($row->material->description)),
            Str::upper(trim($row->batch)),
            (string)$row->quantity,
            Str::upper(trim($row->material->uom)),
            (string)$row->batch_status,
            Str::upper(trim($row->user->nip)),
            (string)$row->creation_date,
            Str::upper(trim($row->material->mtyp))
        ];
    }

    public function collection(): Collection
    {
        $data = $this->batchNotExistService->collection($this->area, $this->period);
        auth()->user()?->notify(new DataExported('Batch Not Exist', $data->count()));
        return $data;
    }
}
