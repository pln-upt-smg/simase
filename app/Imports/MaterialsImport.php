<?php

namespace App\Imports;

use App\Imports\Helper\HasAreaResolver;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasRowCounter;
use App\Imports\Helper\HasValidationException;
use App\Models\Material;
use App\Models\Period;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;

class MaterialsImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithValidation, WithMultipleSheets
{
    use HasValidationException, HasDefaultSheet, HasRowCounter, HasAreaResolver;

    private int $currentAreaId = 0;

    private array $whitelistedMaterialCodes = [];

    private ?Period $period;

    public function __construct(?Period $period)
    {
        $this->period = $period;
    }

    public function rules(): array
    {
        return [
            'area' => ['required', 'string', 'max:255', Rule::exists('areas', 'name')->whereNull('deleted_at')],
            'material' => ['required', 'string', 'max:255'],
            'materialdescription' => ['required', 'string', 'max:255'],
            'uom' => ['required', 'string', 'max:255'],
            'mtyp' => ['required', 'string', 'max:255'],
            'crcy' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'per' => ['required', 'integer', 'min:0']
        ];
    }

    public function uniqueBy(): string|array
    {
        return [
            'material'
        ];
    }

    public function model(array $row): ?Material
    {
        $this->incrementRowCounter();
        $this->lookupArea($row);
        $code = Str::upper(trim($row['material']));
        $this->whitelistedMaterialCodes[] = $code;
        return new Material([
            'area_id' => $this->currentAreaId,
            'period_id' => $this->period?->id ?? 0,
            'code' => $code,
            'description' => Str::title(trim($row['materialdescription'])),
            'uom' => Str::upper(trim($row['uom'])),
            'mtyp' => Str::upper(trim($row['mtyp'])),
            'crcy' => Str::upper(trim($row['crcy'])),
            'price' => (int)$row['price'],
            'per' => (int)$row['per']
        ]);
    }

    private function lookupArea(array $row): void
    {
        $newAreaId = $this->resolveAreaId($row['area']);
        if ($this->currentAreaId !== $newAreaId) {
            $this->currentAreaId = $newAreaId;
            Material::where('area_id', $this->currentAreaId)
                ->where('period_id', $this->period?->id ?? 0)
                ->whereNotIn('code', $this->whitelistedMaterialCodes)
                ->whereNull('deleted_at')
                ->delete();
        }
    }
}
