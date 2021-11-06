<?php

namespace App\Imports;

use App\Imports\Helper\HasBatchInserts;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasMaterialResolver;
use App\Imports\Helper\HasRowCounter;
use App\Imports\Helper\HasValidationException;
use App\Models\Batch;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;

class BatchesImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithValidation, WithEvents, WithMultipleSheets, WithBatchInserts
{
    use HasValidationException, HasDefaultSheet, HasMaterialResolver, HasRowCounter, RegistersEventListeners, HasBatchInserts;

    public function rules(): array
    {
        return [
            'batch' => ['required', 'max:255'],
            'material' => ['required', 'max:255', Rule::exists('materials', 'code')->whereNull('deleted_at')]
        ];
    }

    public function model(array $row): ?Batch
    {
        $this->incrementRowCounter();
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
