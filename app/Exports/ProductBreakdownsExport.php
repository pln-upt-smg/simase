<?php

namespace App\Exports;

use App\Models\Area;
use App\Models\Period;
use App\Notifications\DataExported;
use App\Services\ProductBreakdownService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductBreakdownsExport implements FromCollection, WithHeadings, WithMapping
{
	private ProductBreakdownService $productBreakdownService;

	private ?Area $area;

	private ?Period $period;

	public function __construct(ProductBreakdownService $productBreakdownService, ?Area $area, ?Period $period)
	{
		$this->productBreakdownService = $productBreakdownService;
		$this->area = $area;
		$this->period = $period;
	}

	public function headings(): array
	{
		return [
			'SubArea',
			'Batch',
			'Product',
			'ProductDescription',
			'Material',
			'MaterialDescription'
		];
	}

	public function map($row): array
	{
		return [
			Str::title(trim($row->sub_area_name)),
			Str::upper(trim($row->batch_code)),
			Str::upper(trim($row->product_code)),
			Str::title(trim($row->product_description)),
			Str::upper(trim($row->material_code)),
			Str::title(trim($row->material_description))
		];
	}

	public function collection(): Collection
	{
		$data = $this->productBreakdownService->collection($this->area, $this->period);
		auth()->user()?->notify(new DataExported('FG Material Breakdown', $data->count()));
		return $data;
	}
}
