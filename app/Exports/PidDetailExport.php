<?php

namespace App\Exports;

use App\Models\Period;
use App\Notifications\DataExported;
use App\Services\AreaService;
use App\Services\PidDetailService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PidDetailExport implements FromCollection, WithHeadings, WithMapping
{
    private PidDetailService $pidDetailService;

    private AreaService $areaService;

    private ?Period $period;

    private array $areaStocks;

    public function __construct(PidDetailService $pidDetailService, AreaService $areaService, ?Period $period)
    {
        $this->pidDetailService = $pidDetailService;
        $this->areaService = $areaService;
        $this->period = $period;
        $this->areaStocks = $this->pidDetailService->tableAreaData($this->period);
    }

    public function headings(): array
    {
        return array_merge([
            'Material',
            'MaterialDescription',
            'Batch',
            'Total Of SumOfQuantity'
        ], $this->areaService->collection()->pluck('name')->toArray() ?? []);
    }

    public function map($row): array
    {
        return array_merge([
            Str::upper(trim($row->material_code)),
            Str::title(trim($row->material_description)),
            Str::upper(trim($row->batch_code)),
            Str::upper(trim($row->sum_quantity))
        ], array_map('strval', $this->areaStocks[$row->id] ?? []));
    }

    public function collection(): Collection
    {
        $data = $this->pidDetailService->collection($this->period);
        auth()->user()?->notify(new DataExported('PID Detail', $data->count()));
        return $data;
    }
}
