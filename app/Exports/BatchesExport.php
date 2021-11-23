<?php

namespace App\Exports;

use App\Notifications\DataExported;
use App\Services\BatchService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BatchesExport implements FromCollection, WithHeadings, WithMapping
{
    private BatchService $batchService;

    public function __construct(BatchService $batchService)
    {
        $this->batchService = $batchService;
    }

    public function headings(): array
    {
        return [
            'Batch',
            'Material',
            'SLoc'
        ];
    }

    public function map($row): array
    {
        return [
            Str::upper(trim($row->code)),
            Str::upper(trim($row->material_code)),
            trim($row->sloc)
        ];
    }

    public function collection(): Collection
    {
        $data = $this->batchService->collection();
        auth()->user()?->notify(new DataExported('Batch', $data->count()));
        return $data;
    }
}
