<?php

namespace App\Imports;

use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasValidationException;
use App\Models\Area;
use App\Models\User;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;

class MaterialsImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithValidation, WithEvents, WithMultipleSheets
{
    use HasValidationException, HasDefaultSheet, RegistersEventListeners;

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'uom' => ['required', 'string', 'max:255'],
            'mtyp' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'per' => ['required', 'integer', 'min:0'],
            'area' => ['required', 'int', 'exists:areas,name'],
            'period' => ['required', 'int', 'exists:periods,name']
        ];
    }

    public function uniqueBy(): string|array
    {
        return [
            'code'
        ];
    }

    public function model(array $row): User|null
    {
        return new User([
            'area_id' => $this->resolveAreaId($row['area']),
            'period_id' => $this->resolvePeriodId($row['period']),
            'code' => Str::upper(trim($row['code'])),
            'description' => Str::title(trim($row['description'])),
            'uom' => Str::upper(trim($row['uom'])),
            'mtyp' => Str::upper(trim($row['mtyp'])),
            'crcy' => Str::upper(trim($row['crcy'])),
            'price' => (int)$row['price'],
            'per' => (int)$row['per']
        ]);
    }

    protected function resolveAreaId(string $areaName): int
    {
        return Area::whereRaw('lower(name) = lower(?)', trim($areaName))->whereNull('deleted_at')->first()?->id ?? 0;
    }

    protected function resolvePeriodId(string $periodName): int
    {
        return Area::whereRaw('lower(name) = lower(?)', trim($periodName))->whereNull('deleted_at')->first()?->id ?? 0;
    }

    public static function beforeSheet(): void
    {
        User::whereNull('deleted_at')->delete();
    }
}
