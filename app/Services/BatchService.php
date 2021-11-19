<?php

namespace App\Services;

use App\Exports\BatchesExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\MediaHelper;
use App\Imports\BatchesImport;
use App\Models\Area;
use App\Models\Batch;
use App\Models\Material;
use App\Notifications\DataDestroyed;
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
     * @var SubAreaService
     */
    private SubAreaService $subAreaService;

    /**
     * @var MaterialService $materialService
     */
    private MaterialService $materialService;

    /**
     * @param SubAreaService $subAreaService
     * @param MaterialService $materialService
     */
    public function __construct(SubAreaService $subAreaService, MaterialService $materialService)
    {
        $this->subAreaService = $subAreaService;
        $this->materialService = $materialService;
    }

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        return QueryBuilder::for(Batch::class)
            ->select([
                'batches.id as id',
                'batches.code as batch_code',
                'materials.code as material_code',
                'areas.sloc as sloc'
            ])
            ->leftJoin('areas', 'areas.id', '=', 'batches.area_id')
            ->leftJoin('materials', 'materials.id', '=', 'batches.material_id')
            ->whereNull(['batches.deleted_at', 'areas.deleted_at', 'materials.deleted_at'])
            ->defaultSort('batches.code')
            ->allowedFilters(InertiaHelper::filterBy([
                'batches.code',
                'materials.code',
                'areas.sloc'
            ]))
            ->allowedSorts([
                'batch_code',
                'material_code',
                'sloc'
            ])
            ->paginate()
            ->withQueryString();
    }

    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table->addSearchRows([
            'batches.code' => 'Kode Batch',
            'materials.code' => 'Kode Material',
            'areas.sloc' => 'SLoc'
        ])->addColumns([
            'batch_code' => 'Kode Batch',
            'material_code' => 'Kode Material',
            'sloc' => 'SLoc',
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
            'material_code' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->whereNull('deleted_at')],
            'sloc' => ['required', 'numeric', Rule::exists('areas', 'sloc')->whereNull('deleted_at')]
        ], attributes: [
            'batch_code' => 'Kode Batch',
            'material_code' => 'Kode Material',
            'sloc' => 'SLoc'
        ]);
        Batch::create([
            'area_id' => Area::whereRaw('lower(sloc) = lower(?)', $request->sloc)->first()?->id ?? 0,
            'material_id' => Material::whereRaw('lower(code) = lower(?)', $request->material_code)->whereNull('deleted_at')->first()?->id ?? 0,
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
            'material_code' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->whereNull('deleted_at')],
            'sloc' => ['required', 'numeric', Rule::exists('areas', 'sloc')->whereNull('deleted_at')]
        ], attributes: [
            'batch_code' => 'Kode Batch',
            'material_code' => 'Kode Material',
            'sloc' => 'SLoc'
        ]);
        $batch->updateOrFail([
            'area_id' => Area::whereRaw('lower(sloc) = lower(?)', $request->sloc)->first()?->id ?? 0,
            'material_id' => Material::whereRaw('lower(code) = lower(?)', $request->material_code)->whereNull('deleted_at')->first()?->id ?? 0,
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
        MediaHelper::importSpreadsheet($request, new BatchesImport(auth()->user()));
    }

    /**
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(new BatchesExport, new Batch);
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return 'https://docs.google.com/spreadsheets/d/1B0bHeX76LRUMzFOhODHvgnV32jh5krATVekEq_JMaZI/edit?usp=sharing';
    }

    /**
     * @param Request|null $request
     * @return Batch|null
     */
    public function single(?Request $request = null): ?Batch
    {
        return $this->collection($request)->first();
    }

    /**
     * @param Request|null $request
     * @return Collection
     */
    public function collection(?Request $request = null): Collection
    {
        $query = Batch::select([
            'batches.id as id',
            'batches.code as code',
            'materials.code as material_code',
            'areas.sloc as sloc'
        ])
            ->leftJoin('areas', 'areas.id', 'batches.area_id')
            ->leftJoin('materials', 'materials.id', '=', 'batches.material_id')
            ->leftJoin('sub_areas', 'sub_areas.area_id', '=', 'areas.id')
            ->orderBy('batches.code')
            ->whereNull(['batches.deleted_at', 'areas.deleted_at', 'sub_areas.deleted_at', 'materials.deleted_at']);
        if (!is_null($request)) {
            $query = $query
                ->where('sub_areas.id', $this->subAreaService->resolve($request)?->id ?? 0)
                ->where('materials.id', $this->materialService->resolve($request)?->id ?? 0)
                ->whereRaw('lower(batches.code) like ?', '%' . Str::lower($request->query('q') ?? '') . '%');
        }
        return $query->get();
    }
}
