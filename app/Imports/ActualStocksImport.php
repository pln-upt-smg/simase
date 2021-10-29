<?php

namespace App\Imports;

use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasMaterialResolver;
use App\Imports\Helper\HasValidationException;
use App\Models\ActualStock;
use App\Models\Area;
use App\Models\Period;
use App\Models\User;
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

    private ?User $creator;

    public function __construct(?Area $area, ?Period $period)
    {
        $this->area = $area;
        $this->period = $period;
        $this->creator = auth()->user();
    }

    public function rules(): array
    {
        return [
            'material' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->whereNull('deleted_at')],
            'batch' => ['required', 'string', 'max:255'],
            'materialdescription' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:0'],
            'uom' => ['nullable', 'string', 'max:255'],
            'mtyp' => ['nullable', 'string', 'max:255']
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
            'area_id' => $this->area?->id ?? 0,
            'period_id' => $this->period?->id ?? 0,
            'user_id' => $this->creator?->id ?? 0,
            'material_id' => $this->resolveMaterialId($row['material']),
            'batch' => Str::upper(trim($row['batch'])),
            'quantity' => (int)$row['quantity']
        ]);
    }

    public function registerEvents(): array
    {
        $area = $this->area;
        $period = $this->period;
        return [
            BeforeSheet::class => static function () use ($area, $period) {
                ActualStock::whereNull('deleted_at')
                    ->where('area_id', $area?->id ?? 0)
                    ->where('period_id', $period?->id ?? 0)
                    ->delete();
            }
        ];
    }
}
