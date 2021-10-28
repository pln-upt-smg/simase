<?php

namespace App\Imports;

use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasValidationException;
use App\Models\Area;
use App\Models\BookStock;
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

class BookStocksImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithValidation, WithEvents, WithMultipleSheets
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
            'material' => ['required', 'string', 'max:255', 'exists:materials,code'],
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

    public function model(array $row): BookStock|null
    {
        return new BookStock([
            'material_id' => $this->resolveMaterialId($row['material']),
            'plnt' => (int)$row['plnt'],
            'sloc' => (int)$row['sloc'],
            'batch' => Str::upper(trim($row['batch'])),
            'unrestricted' => (float)$row['unrestricted'],
            'qualinsp' => (int)$row['qualinsp']
        ]);
    }

    protected function resolveMaterialId(string $materialCode): int
    {
        return Material::whereRaw('lower(code) = lower(?)', trim($materialCode))->whereNull('deleted_at')->first()?->id ?? 0;
    }

    public function registerEvents(): array
    {
        $area = $this->area;
        $period = $this->period;
        return [
            BeforeSheet::class => static function () use ($area, $period) {
                BookStock::whereNull('deleted_at')
                    ->whereHas('material', static function ($query) use ($area, $period) {
                        $query->where('area_id', $area?->id ?? 0);
                        $query->where('period_id', $period?->id ?? 0);
                    })
                    ->delete();
            }
        ];
    }
}
