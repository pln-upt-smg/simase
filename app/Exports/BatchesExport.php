<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BatchesExport implements FromCollection, WithHeadings, WithMapping
{
	private Collection $collection;

	public function __construct(Collection $collection)
	{
		$this->collection = $collection;
	}

	public function headings(): array
	{
		return [
			'Batch',
			'Material',
			'SLoc'
		];
	}

	public function map($row): array
	{
		return [
			Str::upper(trim($row->code)),
			Str::upper(trim($row->material_code)),
			trim($row->sloc)
		];
	}

	public function collection(): Collection
	{
		return $this->collection;
	}
}
