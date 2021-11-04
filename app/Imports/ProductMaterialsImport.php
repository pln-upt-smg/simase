<?php

namespace App\Imports;

use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasMaterialResolver;
use App\Imports\Helper\HasProductResolver;
use App\Imports\Helper\HasRowCounter;
use App\Imports\Helper\HasValidationException;
use App\Models\Area;
use App\Models\Period;
use App\Models\ProductMaterial;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\BeforeSheet;

class ProductMaterialsImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithValidation, WithEvents, WithMultipleSheets
{
    use HasValidationException, HasDefaultSheet, HasProductResolver, HasMaterialResolver, HasRowCounter;

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
        return new ProductMaterial([
            'product_id' => $this->resolveProductId($row['product']),
            'material_id' => $this->resolveMaterialId($row['material']),
            'material_uom' => Str::upper(trim($row['uom'])),
            'material_quantity' => (int)$row['qty'],
            'product_quantity' => (int)$row['productqty']
        ]);
    }

    public function registerEvents(): array
    {
        $area = $this->area;
        $period = $this->period;
        return [
            BeforeSheet::class => static function () use ($area, $period) {
                ProductMaterial::leftJoin('products', 'products.id', '=', 'product_materials.product_id')
                    ->leftJoin('materials', 'materials.id', '=', 'product_materials.material_id')
                    ->where('products.area_id', $area?->id ?? 0)
                    ->where('products.period_id', $period?->id ?? 0)
                    ->where('materials.area_id', $area?->id ?? 0)
                    ->where('materials.period_id', $period?->id ?? 0)
                    ->whereNull('product_materials.deleted_at')
                    ->delete();
            }
        ];
    }
}
