<?php

namespace App\Exports;

use App\Models\Area;
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
    private ?Area $area;

    private ?Period $period;

    private MaterialService $materialService;

    public function __construct(?Area $area, ?Period $period, MaterialService $materialService)
    {
        $this->area = $area;
        $this->period = $period;
        $this->materialService = $materialService;
    }

    public function headings(): array
    {
        return [
            'Area',
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
            Str::title(trim($row->area->name)),
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
        $data = $this->materialService->collection($this->area, $this->period);
        auth()->user()?->notify(new DataExported('Material', $data->count()));
        return $data;
    }
}
