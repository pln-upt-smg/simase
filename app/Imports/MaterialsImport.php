<?php

namespace App\Imports;

use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasRowCounter;
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
    use HasValidationException, HasDefaultSheet, HasRowCounter;

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
        return new Material([
            'area_id' => $this->area?->id ?? 0,
            'period_id' => $this->period?->id ?? 0,
            'code' => Str::upper(trim($row['material'])),
            'description' => Str::title(trim($row['materialdescription'])),
            'uom' => Str::upper(trim($row['uom'])),
            'mtyp' => Str::upper(trim($row['mtyp'])),
            'crcy' => Str::upper(trim($row['crcy'])),
            'price' => (int)$row['price'],
            'per' => (int)$row['per']
        ]);
    }

    public function registerEvents(): array
    {
        $area = $this->area;
        $period = $this->period;
        return [
            BeforeSheet::class => static function () use ($area, $period) {
                Material::where('area_id', $area?->id ?? 0)
                    ->where('period_id', $period?->id ?? 0)
                    ->whereNull('deleted_at')
                    ->delete();
            }
        ];
    }
}
