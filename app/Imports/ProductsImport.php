<?php

namespace App\Imports;

use App\Imports\Helper\HasAreaResolver;
use App\Imports\Helper\HasBatchSize;
use App\Imports\Helper\HasChunkSize;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasRowCounter;
use App\Imports\Helper\HasValidationException;
use App\Models\Period;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithValidation, WithMultipleSheets, WithBatchInserts, WithChunkReading, WithUpserts
{
    use HasValidationException, HasDefaultSheet, HasRowCounter, HasAreaResolver, HasBatchSize, HasChunkSize;

    private int $currentAreaId = 0;

    private array $whitelistedProductCodes = [];

    private ?Period $period;

    public function __construct(?Period $period)
    {
        $this->period = $period;
    }

    public function rules(): array
    {
        return [
            'area' => ['required', 'max:255', Rule::exists('areas', 'name')->whereNull('deleted_at')],
            'product' => ['required', 'max:255'],
            'productdescription' => ['nullable', 'max:255'],
            'uom' => ['required', 'max:255'],
            'mtyp' => ['required', 'max:255'],
            'crcy' => ['required', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'per' => ['required', 'integer', 'min:0']
        ];
    }

    public function uniqueBy(): string|array
    {
        return [
            'product'
        ];
    }

    public function model(array $row): ?Product
    {
        $this->incrementRowCounter();
        $this->lookupArea($row);
        $code = Str::upper(trim($row['product']));
        $this->whitelistedProductCodes[] = $code;
        return new Product([
            'area_id' => $this->currentAreaId,
            'period_id' => $this->period?->id ?? 0,
            'code' => $code,
            'description' => Str::title(trim($row['productdescription'])),
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
            Product::where('area_id', $this->currentAreaId)
                ->where('period_id', $this->period?->id ?? 0)
                ->whereNotIn('code', $this->whitelistedProductCodes)
                ->whereNull('deleted_at')
                ->delete();
        }
    }
}
