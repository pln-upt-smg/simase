<?php

namespace App\Imports;

use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasValidationException;
use App\Models\Area;
use App\Models\Material;
use App\Models\Period;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\BeforeSheet;

class MaterialsImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithValidation, WithEvents, WithMultipleSheets
{
    use HasValidationException, HasDefaultSheet;

    private ?Area $area;

    private ?Period $period;

    public function __construct(?Area $area, ?Period $period)
    {
        $this->area = $area;
        $this->period = $period;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
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
            'code'
        ];
    }

    public function model(array $row): Material|null
    {
        return new Material([
            'area_id' => $this->area?->id ?? 0,
            'period_id' => $this->period?->id ?? 0,
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

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function () {
                Material::whereNull('deleted_at')
                    ->where('area_id', $this->area?->id ?? 0)
                    ->where('period_id', $this->period?->id ?? 0)
                    ->delete();
            }
        ];
    }
}
