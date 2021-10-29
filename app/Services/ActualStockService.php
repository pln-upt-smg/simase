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
     * @param bool $ownedByCurrentUser
     * @return LengthAwarePaginator
     */
    public function tableData(?Area $area, ?Period $period, bool $ownedByCurrentUser = false): LengthAwarePaginator
    {
        $query = QueryBuilder::for(ActualStock::class)
            ->select([
                'actual_stocks.id as id',
                'actual_stocks.batch as batch',
                'actual_stocks.quantity as quantity',
                'materials.area_id as area_id',
                'materials.period_id as period_id',
                'materials.code as material_code',
                'materials.description as material_description',
                'materials.uom as uom',
                'materials.mtyp as mtyp',
                'users.id as creator_id',
                'users.name as creator_name'
            ])
            ->leftJoin('materials', 'materials.id', '=', 'actual_stocks.material_id')
            ->leftJoin('users', 'users.id', '=', 'actual_stocks.user_id')
            ->whereNull('actual_stocks.deleted_at')
            ->whereNull('materials.deleted_at');

        if (!is_null($area)) {
            $query = $query->where('materials.area_id', $area->id);
        }

        if (!is_null($period)) {
            $query = $query->where('materials.period_id', $period->id);
        }

        if ($ownedByCurrentUser) {
            $query = $query->where('users.id', auth()->user()?->id ?? 0);
        }

        return $query->defaultSort('material_code')
            ->allowedSorts([
                'batch',
                'quantity',
                'material_code',
                'material_description',
                'uom',
                'mtyp',
                'creator_name'
            ])
            ->allowedFilters(InertiaHelper::filterBy([
                'actual_stocks.batch',
                'actual_stocks.quantity',
                'materials.code',
                'materials.description',
                'materials.uom',
                'materials.mtyp',
                'users.name'
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
            'actual_stocks.batch' => 'Kode Batch',
            'actual_stocks.quantity' => 'Kuantitas',
            'materials.code' => 'Kode Material',
            'materials.description' => 'Deskripsi Material',
            'materials.uom' => 'UoM',
            'materials.mtyp' => 'MType',
            'users.name' => 'Pembuat'
        ])->addFilter(
            'uom',
            'UoM',
            Material::select('uom')->groupBy('uom')->get()->pluck('uom', 'uom')->toArray()
        )->addFilter(
            'mtyp',
            'MType',
            Material::select('mtyp')->groupBy('mtyp')->get()->pluck('mtyp', 'mtyp')->toArray()
        )->addColumns([
            'material_code' => 'Kode Material',
            'batch' => 'Batch',
            'material_description' => 'Deskripsi Material',
            'quantity' => 'Kuantitas',
            'uom' => 'UoM',
            'mtyp' => 'MType',
            'creater_name' => 'Pembuat',
            'action' => 'Aksi'
        ]);
    }

    /**
     * @param Request $request
     * @throws ValidationException
     */
    public function store(Request $request): void
    {
        $request->sku ? $this->storeBySkuCode($request) : $this->storeByMaterialCode($request);
    }

    /**
     * @param Request $request
     * @throws ValidationException
     */
    private function storeByMaterialCode(Request $request): void
    {
        $this->validate($request, [
            'area' => ['required', 'integer', Rule::exists('areas', 'id')->whereNull('deleted_at')],
            'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
            'material_code' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at')],
            'batch' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:0']
        ]);
        ActualStock::create([
            'area_id' => (int)$request->area,
            'period_id' => (int)$request->period,
            'material_id' => Material::whereId($request->material_code)->first()?->id ?? 0,
            'user_id' => auth()->user()?->id ?? 0,
            'batch' => Str::upper($request->batch),
            'quantity' => (int)$request->quantity
        ]);
    }

    /**
     * TODO: implement the store process by product code
     *
     * @param Request $request
     * @throws ValidationException
     */
    private function storeBySkuCode(Request $request): void
    {
        $this->validate($request, [
            'area' => ['required', 'integer', Rule::exists('areas', 'id')->whereNull('deleted_at')],
            'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
            'product_code' => ['required', 'string', 'max:255', Rule::exists('products', 'code')->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at')],
            'batch' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:0']
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
            'material_code' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at')],
            'batch' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:0']
        ]);
        $actual->updateOrFail([
            'area_id' => (int)$request->area,
            'period_id' => (int)$request->period,
            'material_id' => Material::whereId($request->material_code)->first()?->id ?? 0,
            'batch' => Str::upper($request->batch),
            'quantity' => (int)$request->quantity
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
        ), new ActualStock);
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return 'https://docs.google.com/spreadsheets/d/1YNy7flR0ALyR0GAdPfVKVYlXiEWvJobaRrVnKdPrW6M/edit?usp=sharing';
    }
}
