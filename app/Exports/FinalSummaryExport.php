<?php

namespace App\Exports;

use App\Models\Period;
use App\Notifications\DataExported;
use App\Services\FinalSummaryService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FinalSummaryExport implements FromCollection, WithHeadings, WithMapping
{
    private ?Period $period;

    private FinalSummaryService $finalSummaryService;

    public function __construct(?Period $period, FinalSummaryService $finalSummaryService)
    {
        $this->period = $period;
        $this->finalSummaryService = $finalSummaryService;
    }

    public function headings(): array
    {
        return [
            'Material',
            'MaterialDescription',
            'UOM',
            'MTyp',
            'Unrestricted',
            'QualInsp',
            'TotalStock',
            'GapStock',
            'GapValue'
        ];
    }

    public function map($row): array
    {
        return [
            Str::upper(trim($row->material_code)),
            Str::title(trim($row->material_description)),
            Str::upper(trim($row->uom)),
            Str::upper(trim($row->mtyp)),
            (string)$row->unrestricted,
            (string)$row->qualinsp,
            (string)$row->total_stock,
            (string)$row->gap_stock,
            (string)$row->gap_value
        ];
    }

    public function collection(): Collection
    {
        $data = $this->finalSummaryService->collection($this->period);
        auth()->user()?->notify(new DataExported('Final Summary', $data->count()));
        return $data;
    }
}
