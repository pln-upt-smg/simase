<?php

namespace App\Services;

use App\Exports\BatchesExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\MediaHelper;
use App\Imports\BatchesImport;
use App\Models\Batch;
use App\Models\Material;
use App\Notifications\DataDestroyed;
use App\Notifications\DataImported;
use App\Notifications\DataStored;
use App\Notifications\DataUpdated;
use App\Services\Helper\HasValidator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class BatchService
{
    use HasValidator;

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        return QueryBuilder::for(Batch::class)
            ->select([
                'batches.id as id',
                'batches.code as batch_code',
                'materials.code as material_code'
            ])
            ->leftJoin('materials', 'materials.id', '=', 'batches.material_id')
            ->whereNull(['batches.deleted_at', 'materials.deleted_at'])
            ->defaultSort('batches.code')
            ->allowedFilters(InertiaHelper::filterBy([
                'batches.code',
                'materials.code'
            ]))
            ->allowedSorts([
                'batch_code',
                'material_code'
            ])
            ->paginate()
            ->withQueryString();
    }

    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table->addSearchRows([
            'batches.code' => 'Kode Batch',
            'materials.code' => 'Kode Material'
        ])->addColumns([
            'batch_code' => 'Kode Batch',
            'material_code' => 'Kode Material',
            'action' => 'Aksi'
        ]);
    }

    /**
     * @param Request $request
     * @throws ValidationException
     */
    public function store(Request $request): void
    {
        $this->validate($request, [
            'batch_code' => ['required', 'string', 'max:255'],
            'material_code' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->whereNull('deleted_at')]
        ], attributes: [
            'batch_code' => 'Kode Batch',
            'material_code' => 'Kode Material'
        ]);
        Batch::create([
            'material_id' => Material::whereCode($request->material_code)->first()?->id ?? 0,
            'code' => Str::upper($request->batch_code)
        ]);
        auth()->user()?->notify(new DataStored('Batch', Str::upper($request->batch_code)));
    }

    /**
     * @param Request $request
     * @param Batch $batch
     * @throws Throwable
     */
    public function update(Request $request, Batch $batch): void
    {
        $this->validate($request, [
            'batch_code' => ['required', 'string', 'max:255'],
            'material_code' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->whereNull('deleted_at')]
        ], attributes: [
            'batch_code' => 'Kode Batch',
            'material_code' => 'Kode Material'
        ]);
        $batch->updateOrFail([
            'material_id' => Material::whereRaw('lower(code) = lower(?)', $request->material_code)->first()?->id ?? 0,
            'code' => Str::upper($request->batch_code)
        ]);
        $batch->save();
        auth()->user()?->notify(new DataUpdated('Batch', Str::upper($request->batch_code)));
    }

    /**
     * @param Batch $batch
     * @throws Throwable
     */
    public function destroy(Batch $batch): void
    {
        $data = $batch->code;
        $batch->deleteOrFail();
        auth()->user()?->notify(new DataDestroyed('Batch', $data));
    }

    /**
     * @param Request $request
     * @throws Throwable
     */
    public function import(Request $request): void
    {
        $import = new BatchesImport;
        MediaHelper::importSpreadsheet($request, $import);
        auth()->user()?->notify(new DataImported('Batch', $import->getRowCount()));
    }

    /**
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(new BatchesExport($this), new Batch);
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return 'https://docs.google.com/spreadsheets/d/1B0bHeX76LRUMzFOhODHvgnV32jh5krATVekEq_JMaZI/edit?usp=sharing';
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return Batch::orderBy('code')->whereNull('deleted_at')->get()->load('material');
    }
}
