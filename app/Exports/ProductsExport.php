<?php

namespace App\Exports;

use App\Models\Period;
use App\Notifications\DataExported;
use App\Services\ProductService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    private ProductService $productService;

    private ?Period $period;

    public function __construct(ProductService $productService, ?Period $period)
    {
        $this->productService = $productService;
        $this->period = $period;
    }

    public function headings(): array
    {
        return [
            'Product',
            'ProductDescription',
            'UOM',
            'MTyp',
            'Crcy',
            'Price',
            'Per',
            'LastChange'
        ];
    }

    public function map($row): array
    {
        return [
            Str::upper(trim($row->code)),
            Str::title(trim($row->description)),
            Str::upper(trim($row->uom)),
            Str::upper(trim($row->mtyp)),
            Str::upper(trim($row->crcy)),
            (string)$row->price,
            (string)$row->per,
            (string)$row->updated_at->format('d-M-y')
        ];
    }

    public function collection(): Collection
    {
        $data = $this->productService->collection(period: $this->period);
        auth()->user()?->notify(new DataExported('FG Master', $data->count()));
        return $data;
    }
}
