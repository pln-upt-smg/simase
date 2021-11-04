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
        return array_merge([
            'Material',
            'MaterialDescription',
            'Batch',
            'Total Of SumOfQuantity'
        ], $this->areaService->collection()->pluck('name')->toArray() ?? []);
    }

    public function map($row): array
    {
        $areaStocks = $this->areaStocks[$row->id] ?? [];
        $areaStocks = array_map('strval', $areaStocks);
        return array_merge([
            Str::upper(trim($row->material_code)),
            Str::title(trim($row->material_description)),
            Str::upper(trim($row->batch_code)),
            Str::upper(trim($row->sum_quantity))
        ], $areaStocks);
    }

    public function collection(): Collection
    {
        $data = $this->pidDetailService->collection($this->period);
        auth()->user()?->notify(new DataExported('PID Detail', $data->count()));
        return $data;
    }
}
