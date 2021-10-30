<?php

namespace App\Exports;

use App\Models\Period;
use App\Services\AreaService;
use App\Services\PidDetailService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PidDetailExport implements FromCollection, WithHeadings, WithMapping
{
    private ?Period $period;

    private AreaService $areaService;

    private PidDetailService $pidDetailService;

    private array $areaStocks;

    public function __construct(?Period $period, AreaService $areaService, PidDetailService $pidDetailService)
    {
        $this->period = $period;
        $this->areaService = $areaService;
        $this->pidDetailService = $pidDetailService;
        $this->areaStocks = $this->pidDetailService->tableAreaData($this->period);
    }

    public function headings(): array
    {
        $areas = $this->areaService->collection()->toArray();
        return array_merge([
            'Material',
            'MaterialDescription',
            'Batch',
            'Total Of SumOfQuantity'
        ], $areas);
    }

    public function map($row): array
    {
        $data = $this->areaStocks[$row->id];
        return array_merge([
            Str::upper(trim($row->material_code)),
            Str::title(trim($row->material_description)),
            Str::upper(trim($row->batch)),
            Str::upper(trim($row->sum_quantity))
        ], $data);
    }

    public function collection(): Collection
    {
        return $this->pidDetailService->collection($this->period);
    }
}
