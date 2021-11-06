<?php

namespace App\Imports;

use App\Imports\Helper\HasAreaResolver;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasMaterialResolver;
use App\Imports\Helper\HasRowCounter;
use App\Imports\Helper\HasValidationException;
use App\Models\ActualStock;
use App\Models\Period;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;

class ActualStocksImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithValidation, WithMultipleSheets
{
    use HasValidationException, HasDefaultSheet, HasMaterialResolver, HasRowCounter, HasAreaResolver;

    private int $currentAreaId;

    private array $whitelistedMaterialCodes;

    private ?Period $period;

    private ?User $creator;

    public function __construct(?Period $period)
    {
        $this->period = $period;
        $this->creator = auth()->user();
    }

    public function rules(): array
    {
        return [
            'area' => ['required', 'string', 'max:255', Rule::exists('areas', 'name')->whereNull('deleted_at')],
            'material' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->where('area_id', $this->area?->id ?? 0)->where('period_id', $this->period?->id ?? 0)->whereNull('deleted_at')],
            'batch' => ['required', 'string', 'max:255'],
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

    public function model(array $row): ?ActualStock
    {
        $this->incrementRowCounter();
        $this->lookupArea($row);
        $code = Str::upper(trim($row['material']));
        $this->whitelistedMaterialCodes[] = $code;
        return new ActualStock([
            'user_id' => $this->creator?->id ?? 0,
            'material_id' => $this->resolveMaterialId($code),
            'batch' => Str::upper(trim($row['batch'])),
            'quantity' => $row['quantity']
        ]);
    }

    private function lookupArea(array $row): void
    {
        $newAreaId = $this->resolveAreaId($row['area']);
        if (empty($this->currentAreaId) || $this->currentAreaId !== $newAreaId) {
            $this->currentAreaId = $newAreaId;
            ActualStock::leftJoin('materials', 'materials.id', '=', 'actual_stocks.material_id')
                ->where('materials.area_id', $this->currentAreaId)
                ->where('materials.period_id', $period?->id ?? 0)
                ->whereNotIn('materials.code', $this->whitelistedMaterialCodes)
                ->whereNull('actual_stocks.deleted_at')
                ->delete();
        }
    }
}
