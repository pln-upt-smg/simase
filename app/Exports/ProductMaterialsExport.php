<?php

namespace App\Exports;

use App\Models\Period;
use App\Notifications\DataExported;
use App\Services\ProductMaterialService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductMaterialsExport implements FromCollection, WithHeadings, WithMapping
{
    private ProductMaterialService $productMaterialService;

    private ?Period $period;

    public function __construct(ProductMaterialService $productMaterialService, ?Period $period)
    {
        $this->productMaterialService = $productMaterialService;
        $this->period = $period;
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
        $data = $this->productMaterialService->collection($this->period);
        auth()->user()?->notify(new DataExported('FG to Material', $data->count()));
        return $data;
    }
}
