<?php

namespace App\Imports;

use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasValidationException;
use App\Models\Area;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;

class AreasImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithValidation, WithEvents, WithMultipleSheets
{
    use HasValidationException, HasDefaultSheet, RegistersEventListeners;

    public function rules(): array
    {
        return [
            'areaid' => ['required', 'integer', 'min:1'],
            'areadescription' => ['required', 'string', 'max:255']
        ];
    }

    public function uniqueBy(): string|array
    {
        return [
            'areaid',
            'areadescription'
        ];
    }

    public function model(array $row): Area|null
    {
        return new Area([
            'name' => Str::title(trim($row['areadescription']))
        ]);
    }

    public static function beforeSheet(): void
    {
        Area::whereNull('deleted_at')->delete();
    }
}
