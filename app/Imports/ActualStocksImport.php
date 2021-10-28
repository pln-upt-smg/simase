<?php

namespace App\Imports;

use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasMaterialResolver;
use App\Imports\Helper\HasValidationException;
use App\Models\ActualStock;
use App\Models\Area;
use App\Models\Period;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\BeforeSheet;

class ActualStocksImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithValidation, WithEvents, WithMultipleSheets
{
    use HasValidationException, HasDefaultSheet, HasMaterialResolver;

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
            'material' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->whereNull('deleted_at')],
            'plnt' => ['required', 'integer', 'min:0'],
            'sloc' => ['required', 'integer', 'min:0'],
            'batch' => ['required', 'string', 'max:255'],
            'unrestricted' => ['required', 'float'],
            'qualinsp' => ['required', 'integer', 'min:0'],
            'materialdescription' => ['required', 'string', 'max:255'],
            'uom' => ['required', 'string', 'max:255'],
            'mtyp' => ['required', 'string', 'max:255']
        ];
    }

    public function uniqueBy(): string|array
    {
        return [
            'material'
        ];
    }

    public function model(array $row): ActualStock|null
    {
        return new ActualStock([
            'material_id' => $this->resolveMaterialId($row['material']),
            'plnt' => (int)$row['plnt'],
            'sloc' => (int)$row['sloc'],
            'batch' => Str::upper(trim($row['batch'])),
            'unrestricted' => (float)$row['unrestricted'],
            'qualinsp' => (int)$row['qualinsp']
        ]);
    }

    public function registerEvents(): array
    {
        $area = $this->area;
        $period = $this->period;
        return [
            BeforeSheet::class => static function () use ($area, $period) {
                ActualStock::whereNull('deleted_at')
                    ->whereHas('material', static function ($query) use ($area, $period) {
                        $query->where('area_id', $area?->id ?? 0);
                        $query->where('period_id', $period?->id ?? 0);
                    })
                    ->delete();
            }
        ];
    }
}
