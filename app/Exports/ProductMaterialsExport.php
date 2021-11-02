<?php

namespace App\Exports;

use App\Models\Area;
use App\Models\Period;
use App\Services\ProductMaterialService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductMaterialsExport implements FromCollection, WithHeadings, WithMapping
{
    private ?Area $area;

    private ?Period $period;

    private ProductMaterialService $productMaterialService;

    public function __construct(?Area $area, ?Period $period, ProductMaterialService $productMaterialService)
    {
        $this->area = $area;
        $this->period = $period;
        $this->productMaterialService = $productMaterialService;
    }

    public function headings(): array
    {
        return [
            'Product',
            'ProductDescription',
            'ProductQty',
            'Material',
            'MaterialDescription',
            'UoM',
            'Qty'
        ];
    }

    public function map($row): array
    {
        return [
            Str::upper(trim($row->product->code)),
            Str::title(trim($row->product->description)),
            (string)$row->product_quantity,
            Str::upper(trim($row->material->code)),
            Str::title(trim($row->material->description)),
            Str::upper(trim($row->material_uom)),
            (string)$row->material_quantity,
        ];
    }

    public function collection(): Collection
    {
        return $this->productMaterialService->collection($this->area, $this->period);
    }
}
