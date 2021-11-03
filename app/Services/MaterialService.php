<?php

namespace App\Services;

use App\Exports\MaterialsExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\MediaHelper;
use App\Imports\MaterialsImport;
use App\Models\Area;
use App\Models\Material;
use App\Models\Period;
use App\Notifications\DataDestroyed;
use App\Notifications\DataImported;
use App\Notifications\DataStored;
use App\Notifications\DataUpdated;
use App\Services\Helper\HasValidator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class MaterialService
{
    use HasValidator;

    /**
     * @var AreaService
     */
    private AreaService $areaService;

    /**
     * @var PeriodService
     */
    private PeriodService $periodService;

    /**
     * @param AreaService $areaService
     * @param PeriodService $periodService
     */
    public function __construct(AreaService $areaService, PeriodService $periodService)
    {
        $this->areaService = $areaService;
        $this->periodService = $periodService;
    }

    /**
     * @param Area|null $area
     * @param Period|null $period
     * @return LengthAwarePaginator
     */
    public function tableData(?Area $area, ?Period $period): LengthAwarePaginator
    {
        $query = QueryBuilder::for(Material::class)
            ->select([
                'materials.id as id',
                'materials.area_id as area_id',
                'materials.period_id as period_id',
                'materials.code as code',
                'materials.description as description',
                'materials.uom as uom',
                'materials.mtyp as mtyp',
                'materials.crcy as crcy',
                'materials.price as price',
                'materials.per as per',
                DB::raw('date_format(materials.updated_at, "%d %b %Y") as update_date')
            ])
            ->whereNull('materials.deleted_at');
        if (!is_null($area)) {
            $query = $query->leftJoin('areas', 'areas.id', '=', 'materials.area_id')
                ->where('areas.id', $area->id)
                ->whereNull('areas.deleted_at');
        }
        if (!is_null($period)) {
            $query = $query->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
                ->where('periods.id', $period->id)
                ->whereNull('periods.deleted_at');
        }
        return $query->defaultSort('materials.code')
            ->allowedFilters(InertiaHelper::filterBy([
                'materials.code',
                'materials.description',
                'materials.uom',
                'materials.mtyp',
                'materials.crcy',
                'materials.price',
                'materials.per'
            ]))
            ->allowedSorts([
                'code',
                'description',
                'uom',
                'mtyp',
                'crcy',
                'price',
                'per',
                'update_date'
            ])
            ->paginate()
            ->withQueryString();
    }

    /**
     * @param InertiaTable $table
     * @return InertiaTable
     */
    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table->addSearchRows([
            'materials.code' => 'Kode Material',
            'materials.description' => 'Deskripsi Material',
            'materials.uom' => 'UoM',
            'materials.mtyp' => 'MType',
            'materials.crcy' => 'Currency',
            'materials.price' => 'Harga',
            'materials.per' => 'Per'
        ])->addColumns([
            'code' => 'Kode Material',
            'description' => 'Deskripsi Material',
            'uom' => 'UoM',
            'mtyp' => 'MType',
            'crcy' => 'Currency',
            'price' => 'Harga',
            'per' => 'Per',
            'update_date' => 'Tanggal Pembaruan',
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
            'area' => ['required', 'integer', Rule::exists('areas', 'id')->whereNull('deleted_at')],
            'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:255', Rule::unique('materials', 'code')->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at')],
            'description' => ['required', 'string', 'max:255'],
            'uom' => ['required', 'string', 'max:255'],
            'mtyp' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'per' => ['required', 'integer', 'min:0']
        ], attributes: [
            'area' => 'Area',
            'period' => 'Periode',
            'code' => 'Kode Material',
            'description' => 'Deskripsi Material',
            'uom' => 'UoM',
            'mtyp' => 'MType',
            'price' => 'Harga',
            'per' => 'Per'
        ]);
        Material::create([
            'area_id' => (int)$request->area,
            'period_id' => (int)$request->period,
            'code' => Str::upper($request->code),
            'description' => Str::title($request->description),
            'uom' => Str::upper($request->uom),
            'mtyp' => Str::upper($request->mtyp),
            'crcy' => Str::upper($request->crcy),
            'price' => (int)$request->price,
            'per' => (int)$request->per
        ]);
        auth()->user()?->notify(new DataStored('Material', Str::upper($request->code)));
    }

    /**
     * @param Request $request
     * @param Material $material
     * @throws Throwable
     */
    public function update(Request $request, Material $material): void
    {
        $this->validate($request, [
            'area' => ['required', 'integer', Rule::exists('areas', 'id')->whereNull('deleted_at')],
            'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:255', Rule::unique('materials', 'code')->where('area_id', $request->area)->where('period_id', $request->period)->ignore($material->id)->whereNull('deleted_at')],
            'description' => ['required', 'string', 'max:255'],
            'uom' => ['required', 'string', 'max:255'],
            'mtyp' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'per' => ['required', 'integer', 'min:0']
        ], attributes: [
            'area' => 'Area',
            'period' => 'Periode',
            'code' => 'Kode Material',
            'description' => 'Deskripsi Material',
            'uom' => 'UoM',
            'mtyp' => 'MType',
            'price' => 'Harga',
            'per' => 'Per'
        ]);
        $material->updateOrFail([
            'area_id' => (int)$request->area,
            'period_id' => (int)$request->period,
            'code' => Str::upper($request->code),
            'description' => Str::title($request->description),
            'uom' => Str::upper($request->uom),
            'mtyp' => Str::upper($request->mtyp),
            'crcy' => Str::upper($request->crcy),
            'price' => (int)$request->price,
            'per' => (int)$request->per
        ]);
        $material->save();
        auth()->user()?->notify(new DataUpdated('Material', Str::upper($request->code)));
    }

    /**
     * @param Material $material
     * @throws Throwable
     */
    public function destroy(Material $material): void
    {
        $data = $material->code;
        $material->deleteOrFail();
        auth()->user()?->notify(new DataDestroyed('Material', $data));
    }

    /**
     * @param Request $request
     * @throws Throwable
     */
    public function import(Request $request): void
    {
        $this->validate($request, [
            'area' => ['required', 'integer', Rule::exists('areas', 'id')->whereNull('deleted_at')],
            'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
            'file' => ['required', 'mimes:xls,xlsx,csv', 'max:' . MediaHelper::SPREADSHEET_MAX_SIZE]
        ], attributes: [
            'area' => 'Area',
            'period' => 'Periode',
            'file' => 'File'
        ]);
        $import  =new MaterialsImport(
            Area::where('id', (int)$request->area)->first(),
            Period::where('id', (int)$request->period)->first()
        );
        Excel::import($import, $request->file('file'));
        auth()->user()?->notify(new DataImported('Material', $import->getRowCount()));
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(Request $request): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(new MaterialsExport(
            $this->areaService->resolve($request),
            $this->periodService->resolve($request),
            $this
        ), new Material);
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return 'https://docs.google.com/spreadsheets/d/195BdCWN1KWWKSoImWSHFg-9vq67s-O7IsaMV7pAI-Mg/edit?usp=sharing';
    }

    /**
     * @param Area|null $area
     * @param Period|null $period
     * @return Collection
     */
    public function collection(?Area $area, ?Period $period): Collection
    {
        $query = Material::whereNull('materials.deleted_at');
        if (!is_null($area)) {
            $query = $query->leftJoin('areas', 'areas.id', '=', 'materials.area_id')
                ->where('areas.id', $area->id)
                ->whereNull('areas.deleted_at');
        }
        if (!is_null($period)) {
            $query = $query->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
                ->where('periods.id', $period->id)
                ->whereNull('periods.deleted_at');
        }
        return $query->orderBy('materials.code')->get();
    }

    public function resolveMaterialCode(Request $request, bool $strict = true): ?Material
    {
        $area = $this->areaService->resolve($request);
        $period = $this->periodService->resolve($request);
        $query = Material::whereRaw('lower(materials.code) = ?', Str::lower(trim($request->query('q') ?? '')))
            ->whereNull('materials.deleted_at');
        if ($strict) {
            $query = $query->leftJoin('areas', 'areas.id', '=', 'materials.area_id')
                ->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
                ->where('areas.id', $area?->id ?? 0)
                ->where('periods.id', $period?->id ?? 0)
                ->whereNull(['areas.deleted_at', 'periods.deleted_at']);
        } else {
            if (!is_null($area)) {
                $query = $query->leftJoin('areas', 'areas.id', '=', 'materials.area_id')
                    ->where('areas.id', $area->id)
                    ->whereNull('areas.deleted_at');
            }
            if (!is_null($period)) {
                $query = $query->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
                    ->where('periods.id', $period->id)
                    ->whereNull('periods.deleted_at');
            }
        }
        return $query->first();
    }

    public function materialCodeCollection(Request $request, bool $strict = true): Collection
    {
        $area = $this->areaService->resolve($request);
        $period = $this->periodService->resolve($request);
        $query = Material::distinct()
            ->select([
                'materials.code as code',
                'materials.description as description',
                'materials.uom as uom'
            ])
            ->orderBy('materials.code')
            ->whereNull('materials.deleted_at')
            ->whereRaw('lower(materials.code) like ?', '%' . Str::lower(trim($request->query('q') ?? '')) . '%');
        if ($strict) {
            $query = $query->leftJoin('areas', 'areas.id', '=', 'materials.area_id')
                ->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
                ->where('areas.id', $area?->id ?? 0)
                ->where('periods.id', $period?->id ?? 0)
                ->whereNull(['areas.deleted_at', 'periods.deleted_at']);
        } else {
            if (!is_null($area)) {
                $query = $query->leftJoin('areas', 'areas.id', '=', 'materials.area_id')
                    ->where('areas.id', $area->id)
                    ->whereNull('areas.deleted_at');
            }
            if (!is_null($period)) {
                $query = $query->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
                    ->where('periods.id', $period->id)
                    ->whereNull('periods.deleted_at');
            }
        }
        return $query->get();
    }
}
