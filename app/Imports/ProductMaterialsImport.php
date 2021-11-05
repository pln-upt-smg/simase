<?php

namespace App\Imports;

use App\Imports\Helper\HasAreaResolver;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasMaterialResolver;
use App\Imports\Helper\HasProductResolver;
use App\Imports\Helper\HasRowCounter;
use App\Imports\Helper\HasValidationException;
use App\Models\Period;
use App\Models\ProductMaterial;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductMaterialsImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithValidation, WithMultipleSheets
{
    use HasValidationException, HasDefaultSheet, HasProductResolver, HasMaterialResolver, HasRowCounter, HasAreaResolver;

    private int $currentAreaId;

    private array $whitelistedProductMaterialIds;

    private ?Period $period;

    public function __construct(?Period $period)
    {
        $this->period = $period;
    }

    public function rules(): array
    {
        return [
            'area' => ['required', 'string', 'max:255', Rule::exists('areas', 'name')->whereNull('deleted_at')],
            'product' => ['required', 'string', 'max:255', Rule::exists('products', 'code')->where('area_id', $this->area?->id ?? 0)->where('period_id', $this->period?->id ?? 0)->whereNull('deleted_at')],
            'productdescription' => ['nullable', 'string', 'max:255'],
            'productqty' => ['required', 'numeric', 'min:0'],
            'material' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->where('area_id', $this->area?->id ?? 0)->where('period_id', $this->period?->id ?? 0)->whereNull('deleted_at')],
            'materialdescription' => ['nullable', 'string', 'max:255'],
            'uom' => ['required', 'string', 'max:255'],
            'qty' => ['required', 'numeric', 'min:0']
        ];
    }

    public function model(array $row): ?ProductMaterial
    {
        $this->incrementRowCounter();
        $this->lookupArea($row);
        $productMaterial = new ProductMaterial([
            'product_id' => $this->resolveProductId($row['product']),
            'material_id' => $this->resolveMaterialId($row['material']),
            'material_uom' => Str::upper(trim($row['uom'])),
            'material_quantity' => (int)$row['qty'],
            'product_quantity' => (int)$row['productqty']
        ]);
        $productMaterial->save();
        $this->whitelistedProductMaterialIds[] = $productMaterial->fresh()?->id ?? 0;
        return $productMaterial;
    }

    private function lookupArea(array $row): void
    {
        $newAreaId = $this->resolveAreaId($row['area']);
        if ($this->currentAreaId !== $newAreaId) {
            $this->currentAreaId = $newAreaId;
            ProductMaterial::leftJoin('products', 'products.id', '=', 'product_materials.product_id')
                ->leftJoin('materials', 'materials.id', '=', 'product_materials.material_id')
                ->where('products.area_id', $this->currentAreaId)
                ->where('products.period_id', $period?->id ?? 0)
                ->where('materials.area_id', $this->currentAreaId)
                ->where('materials.period_id', $period?->id ?? 0)
                ->whereNotIn('product_materials.id', $this->whitelistedProductMaterialIds)
                ->whereNull('product_materials.deleted_at')
                ->delete();
        }
    }
}
