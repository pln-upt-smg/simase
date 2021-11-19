<?php

namespace App\Exports;

use App\Models\Period;
use App\Notifications\DataExported;
use App\Services\MaterialService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MaterialsExport implements FromCollection, WithHeadings, WithMapping
{
    private MaterialService $materialService;

    private ?Period $period;

    public function __construct(MaterialService $materialService, ?Period $period)
    {
        $this->materialService = $materialService;
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
            (string)$row->updated_at
        ];
    }

    public function collection(): Collection
    {
        $data = $this->materialService->collection(period: $this->period);
        auth()->user()?->notify(new DataExported('Material Master', $data->count()));
        return $data;
    }
}
