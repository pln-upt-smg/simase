<?php

namespace App\Services;

use App\Exports\MaterialsExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\MediaHelper;
use App\Imports\MaterialsImport;
use App\Models\Area;
use App\Models\Material;
use App\Models\Period;
use App\Services\Helper\HasValidator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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

    private AreaService $areaService;

    private PeriodService $periodService;

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
            $query = $query->where('materials.area_id', $area->id);
        }
        if (!is_null($period)) {
            $query = $query->where('materials.period_id', $period->id);
        }
        return $query->defaultSort('code')
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
            ->allowedFilters(InertiaHelper::filterBy([
                'code',
                'description',
                'uom',
                'mtyp',
                'crcy',
                'price',
                'per'
            ]))
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
            'code' => 'Kode Material',
            'description' => 'Deskripsi Material',
            'uom' => 'UoM',
            'mtyp' => 'MType',
            'crcy' => 'Currency',
            'price' => 'Harga',
            'per' => 'Per'
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
    }

    /**
     * @param Request $request
     * @param Material $material
     * @throws Throwable
     * @throws ValidationException
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
    }

    /**
     * @param Material $material
     * @throws Throwable
     */
    public function destroy(Material $material): void
    {
        $material->deleteOrFail();
    }

    /**
     * @param Request $request
     * @throws Throwable
     */
    public function import(Request $request): void
    {
        Validator::make($request->all(), [
            'area' => ['required', 'integer', Rule::exists('areas', 'id')->whereNull('deleted_at')],
            'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
            'file' => ['required', 'mimes:xls,xlsx,csv', 'max:' . MediaHelper::SPREADSHEET_MAX_SIZE]
        ])->validate();
        Excel::import(new MaterialsImport(
            Area::whereId((int)$request->area)->first(),
            Period::whereId((int)$request->period)->first()
        ), $request->file('file'));
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(Request $request): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(new MaterialsExport(
            Area::find(empty($request->query('area')) ? null : (int)$request->query('area')),
            Period::find(empty($request->query('period')) ? null : (int)$request->query('period')),
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
        $query = Material::whereNull('deleted_at');
        if (!is_null($area)) {
            $query = $query->where('area_id', $area->id);
        }
        if (!is_null($period)) {
            $query = $query->where('period_id', $period->id);
        }
        return $query->orderBy('code')->get();
    }

    public function materialCodeCollection(Request $request, bool $strict = true): Collection
    {
        $area = $this->areaService->resolve($request);
        $period = $this->periodService->resolve($request);
        $query = Material::select(['code', 'description', 'uom'])
            ->orderBy('code')
            ->distinct()
            ->whereNull('deleted_at')
            ->whereRaw('lower(code) like ?', '%' . Str::lower(trim($request->query('q') ?? '')) . '%');
        if ($strict) {
            $query = $query->where('area_id', $area?->id ?? 0)->where('period_id', $period?->id ?? 0);
        } else {
            if (!is_null($area)) {
                $query = $query->where('area_id', $area?->id ?? 0);
            }
            if (!is_null($period)) {
                $query = $query->where('period_id', $period?->id ?? 0);
            }
        }
        return $query->get();
    }
}
