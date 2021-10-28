<?php

namespace App\Exports;

use App\Models\Batch;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BatchesExport implements FromCollection, WithHeadings, WithMapping
{
    public function headings(): array
    {
        return [
            'Batch',
            'Material'
        ];
    }

    public function map($row): array
    {
        return [
            Str::upper(trim($row->code)),
            Str::upper(trim($row->material->code))
        ];
    }

    public function collection(): Collection
    {
        return Batch::all()->whereNull('deleted_at')->sortBy('code');
    }
}
