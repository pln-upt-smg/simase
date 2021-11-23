<?php

namespace App\Exports;

use App\Models\Batch;
use App\Notifications\DataExported;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BatchesExport implements FromCollection, WithHeadings, WithMapping
{
//    private BatchService $batchService;
//
//    public function __construct(BatchService $batchService)
//    {
//        $this->batchService = $batchService;
//    }

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
//        $data = $this->batchService->collection();
		$data = Batch::select([
			'batches.id as id',
			'batches.code as code',
			'materials.code as material_code',
			'areas.sloc as sloc'
		])
			->leftJoin('areas', 'areas.id', 'batches.area_id')
			->leftJoin('materials', 'materials.id', '=', 'batches.material_id')
			->leftJoin('sub_areas', 'sub_areas.area_id', '=', 'areas.id')
			->orderBy('batches.code')
			->whereNull(['batches.deleted_at', 'areas.deleted_at', 'sub_areas.deleted_at', 'materials.deleted_at'])
			->get();
		auth()->user()?->notify(new DataExported('Batch', $data->count()));
		return $data;
	}
}
