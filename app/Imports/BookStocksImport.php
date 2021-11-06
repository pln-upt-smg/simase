<?php

namespace App\Imports;

use App\Imports\Helper\HasAreaResolver;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasMaterialResolver;
use App\Imports\Helper\HasRowCounter;
use App\Imports\Helper\HasValidationException;
use App\Models\BookStock;
use App\Models\Period;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;

class BookStocksImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithValidation, WithMultipleSheets
{
    use HasValidationException, HasDefaultSheet, HasMaterialResolver, HasRowCounter, HasAreaResolver;

    private int $currentAreaId;

    private array $whitelistedMaterialCodes;

    private ?Period $period;

    public function __construct(?Period $period)
    {
        $this->period = $period;
    }

    public function rules(): array
    {
        return [
            'area' => ['required', 'string', 'max:255', Rule::exists('areas', 'name')->whereNull('deleted_at')],
            'material' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->where('area_id', $this->area?->id ?? 0)->where('period_id', $this->period?->id ?? 0)->whereNull('deleted_at')],
            'plnt' => ['required', 'integer', 'min:0'],
            'sloc' => ['required', 'integer', 'min:0'],
            'batch' => ['required', 'string', 'max:255'],
            'unrestricted' => ['required', 'numeric'],
            'qualinsp' => ['required', 'integer', 'min:0'],
            'materialdescription' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0'],
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

    public function model(array $row): ?BookStock
    {
        $this->incrementRowCounter();
        $this->lookupArea($row);
        $code = Str::upper(trim($row['material']));
        $this->whitelistedMaterialCodes[] = $code;
        return new BookStock([
            'material_id' => $this->resolveMaterialId($code),
            'batch' => Str::upper(trim($row['batch'])),
            'quantity' => $row['quantity'],
            'plnt' => (int)$row['plnt'],
            'sloc' => (int)$row['sloc'],
            'unrestricted' => $row['unrestricted'],
            'qualinsp' => (int)$row['qualinsp']
        ]);
    }

    private function lookupArea(array $row): void
    {
        $newAreaId = $this->resolveAreaId($row['area']);
        if (empty($this->currentAreaId) || $this->currentAreaId !== $newAreaId) {
            $this->currentAreaId = $newAreaId;
            BookStock::leftJoin('materials', 'materials.id', '=', 'book_stocks.material_id')
                ->where('materials.area_id', $this->currentAreaId)
                ->where('materials.period_id', $this->period?->id ?? 0)
                ->whereNotIn('materials.code', $this->whitelistedMaterialCodes)
                ->whereNull('book_stocks.deleted_at')
                ->delete();
        }
    }
}
