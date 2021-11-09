<?php

namespace App\Exports;

use App\Models\Area;
use App\Notifications\DataExported;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AreasExport implements FromCollection, WithHeadings, WithMapping
{
    public function headings(): array
    {
        return [
            'AreaID',
            'SLoc',
            'Group'
        ];
    }

    public function map($row): array
    {
        return [
            Str::title(trim($row->name)),
            trim($row->sloc),
            Str::title(trim($row->group))
        ];
    }

    public function collection(): Collection
    {
        $data = Area::whereNull('deleted_at')->orderBy('id')->get();
        auth()->user()?->notify(new DataExported('Area', $data->count()));
        return $data;
    }
}
