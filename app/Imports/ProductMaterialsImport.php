<?php

namespace App\Imports;

use App\Imports\Contract\WithDefaultEvents;
use App\Imports\Helper\HasBatchSize;
use App\Imports\Helper\HasChunkSize;
use App\Imports\Helper\HasDefaultEvents;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasImporter;
use App\Imports\Helper\HasMaterialResolver;
use App\Imports\Helper\HasMultipleArea;
use App\Imports\Helper\HasProductResolver;
use App\Models\Period;
use App\Models\ProductMaterial;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductMaterialsImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithMultipleSheets, WithChunkReading, WithBatchInserts, WithEvents, WithDefaultEvents, ShouldQueue, ShouldBeUnique
{
    use HasDefaultSheet, HasDefaultEvents, HasImporter, HasChunkSize, HasBatchSize, HasMultipleArea, HasProductResolver, HasMaterialResolver;

    private int $periodId;

    public function __construct(?Period $period, ?User $user)
    {
        $this->periodId = $period?->id ?? 0;
        $this->userId = $user?->id ?? 0;
    }

    public function rules(): array
    {
        return [
            'area' => ['required', 'max:255', Rule::exists('areas', 'name')->whereNull('deleted_at')],
            'product' => ['required', 'max:255'],
            'productdescription' => ['nullable', 'max:255'],
            'productqty' => ['required', 'numeric', 'min:0'],
            'material' => ['required', 'max:255'],
            'materialdescription' => ['nullable', 'max:255'],
            'uom' => ['required', 'max:255'],
            'qty' => ['required', 'numeric', 'min:0']
        ];
    }

    /**
     * @param array $row
     * @return ProductMaterial|null
     * @throws ValidationException
     */
    public function model(array $row): ?ProductMaterial
    {
        $this->lookupArea($row);
        Validator::validate($row, [
            'product' => [Rule::exists('products', 'code')->where('area_id', $this->currentAreaId)->where('period_id', $this->periodId)->whereNull('deleted_at')],
            'material' => [Rule::exists('materials', 'code')->where('area_id', $this->currentAreaId)->where('period_id', $this->periodId)->whereNull('deleted_at')]
        ]);
        return new ProductMaterial([
            'product_id' => $this->resolveProductId($row['product']),
            'material_id' => $this->resolveMaterialId($row['material']),
            'material_uom' => Str::upper(trim($row['uom'])),
            'material_quantity' => $row['qty'],
            'product_quantity' => $row['productqty']
        ]);
    }

    public function name(): string
    {
        return 'Product Material';
    }

    public function overwrite(): void
    {
        ProductMaterial::leftJoin('products', 'products.id', '=', 'product_materials.product_id')
            ->leftJoin('materials', 'materials.id', '=', 'product_materials.material_id')
            ->where('products.period_id', $this->periodId)
            ->where('materials.period_id', $this->periodId)
            ->whereNull('product_materials.deleted_at')
            ->delete();
    }
}
