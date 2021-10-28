<?php

namespace App\Imports;

use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasMaterialResolver;
use App\Imports\Helper\HasValidationException;
use App\Models\Batch;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;

class BatchesImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithValidation, WithEvents, WithMultipleSheets
{
    use HasValidationException, HasDefaultSheet, HasMaterialResolver, RegistersEventListeners;

    public function rules(): array
    {
        return [
            'batch' => ['required', 'string', 'max:255'],
            'material' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->whereNull('deleted_at')]
        ];
    }

    public function model(array $row): Batch|null
    {
        return new Batch([
            'material_id' => $this->resolveMaterialId($row['material']),
            'code' => Str::upper(trim($row['batch']))
        ]);
    }

    public static function beforeSheet(): void
    {
        Batch::whereNull('deleted_at')->delete();
    }
}