<?php

namespace App\Services;

use App\Exports\ActualStocksExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\MediaHelper;
use App\Imports\ActualStocksImport;
use App\Models\ActualStock;
use App\Models\Area;
use App\Models\Material;
use App\Models\Period;
use App\Services\Helper\HasValidator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ActualStockService
{
    use HasValidator;

    /**
     * @var AreaService
     */
    public AreaService $areaService;

    /**
     * @var PeriodService
     */
    public PeriodService $periodService;

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
        $query = QueryBuilder::for(ActualStock::class)
            ->select([
                'actual_stocks.id as id',
                'actual_stocks.batch as batch',
                'actual_stocks.plnt as plnt',
                'actual_stocks.sloc as sloc',
                'actual_stocks.qualinsp as qualinsp',
                'actual_stocks.unrestricted as unrestricted',
                'materials.area_id as area_id',
                'materials.period_id as period_id',
                'materials.code as code',
                'materials.description as description',
                'materials.uom as uom',
                'materials.mtyp as mtyp'
            ])
            ->leftJoin('materials', 'materials.id', '=', 'actual_stocks.material_id')
            ->whereNull('actual_stocks.deleted_at')
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
                'batch',
                'plnt',
                'sloc',
                'qualinsp',
                'unrestricted'
            ])
            ->allowedFilters(InertiaHelper::filterBy([
                'actual_stocks.batch',
                'actual_stocks.plnt',
                'actual_stocks.sloc',
                'actual_stocks.qualinsp',
                'actual_stocks.unrestricted',
                'materials.code',
                'materials.description',
                'materials.uom',
                'materials.mtyp'
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
            'materials.code' => 'Kode Material',
            'materials.description' => 'Deskripsi Material',
            'materials.uom' => 'UoM',
            'materials.mtyp' => 'MType',
            'actual_stocks.batch' => 'Batch',
            'actual_stocks.plnt' => 'Plnt',
            'actual_stocks.sloc' => 'SLoc',
            'actual_stocks.qualinsp' => 'QualInsp',
            'actual_stocks.unrestricted' => 'Unrestricted'
        ])->addFilter(
            'materials.uom',
            'UoM',
            Material::select('uom')->groupBy('uom')->get()->pluck('uom', 'uom')->toArray()
        )->addFilter(
            'materials.mtyp',
            'MType',
            Material::select('mtyp')->groupBy('mtyp')->get()->pluck('mtyp', 'mtyp')->toArray()
        )->addColumns([
            'code' => 'Kode Material',
            'description' => 'Deskripsi Material',
            'uom' => 'UoM',
            'mtyp' => 'MType',
            'batch' => 'Batch',
            'plnt' => 'Plnt',
            'sloc' => 'SLoc',
            'qualinsp' => 'QualInsp',
            'unrestricted' => 'Unrestricted',
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
            'code' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->whereNull('deleted_at')],
            'batch' => ['required', 'string', 'max:255'],
            'plnt' => ['required', 'integer', 'min:0'],
            'sloc' => ['required', 'integer', 'min:0'],
            'qualinsp' => ['required', 'integer', 'min:0'],
            'unrestricted' => ['required', 'float']
        ]);
        ActualStock::create([
            'area_id' => $request->area,
            'period_id' => $request->period,
            'material_id' => Material::whereId($request->code)->first()?->id ?? 0,
            'batch' => Str::upper($request->batch),
            'plnt' => $request->plnt,
            'sloc' => $request->sloc,
            'qualinsp' => $request->qualinsp,
            'unrestricted' => $request->unrestricted
        ]);
    }

    /**
     * @param Request $request
     * @param ActualStock $actual
     * @throws Throwable
     * @throws ValidationException
     */
    public function update(Request $request, ActualStock $actual): void
    {
        $this->validate($request, [
            'area' => ['required', 'integer', Rule::exists('areas', 'id')->whereNull('deleted_at')],
            'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->whereNull('deleted_at')],
            'batch' => ['required', 'string', 'max:255'],
            'plnt' => ['required', 'integer', 'min:0'],
            'sloc' => ['required', 'integer', 'min:0'],
            'qualinsp' => ['required', 'integer', 'min:0'],
            'unrestricted' => ['required', 'float']
        ]);
        $actual->updateOrFail([
            'area_id' => $request->area,
            'period_id' => $request->period,
            'material_id' => Material::whereId($request->code)->first()?->id ?? 0,
            'batch' => Str::upper($request->batch),
            'plnt' => $request->plnt,
            'sloc' => $request->sloc,
            'qualinsp' => $request->qualinsp,
            'unrestricted' => $request->unrestricted
        ]);
        $actual->save();
    }

    /**
     * @param ActualStock $actual
     * @throws Throwable
     */
    public function destroy(ActualStock $actual): void
    {
        $actual->deleteOrFail();
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
        Excel::import(new ActualStocksImport(
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
        return MediaHelper::exportSpreadsheet(new ActualStocksExport(
            Area::find(empty($request->query('area')) ? null : (int)$request->query('area')),
            Period::find(empty($request->query('period')) ? null : (int)$request->query('period'))
        ), new Material);
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return 'https://docs.google.com/spreadsheets/d/1YNy7flR0ALyR0GAdPfVKVYlXiEWvJobaRrVnKdPrW6M/edit?usp=sharing';
    }
}