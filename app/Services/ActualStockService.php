<?php

namespace App\Services;

use App\Exports\ActualStocksExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\JobHelper;
use App\Http\Helper\MediaHelper;
use App\Imports\ActualStocksImport;
use App\Models\ActualStock;
use App\Models\Area;
use App\Models\Material;
use App\Models\Period;
use App\Models\Product;
use App\Notifications\DataDestroyed;
use App\Notifications\DataStored;
use App\Notifications\DataUpdated;
use App\Services\Helper\HasValidator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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
     * @param bool $ownedByCurrentUser
     * @return LengthAwarePaginator
     */
    public function tableData(?Area $area, ?Period $period, bool $ownedByCurrentUser = false): LengthAwarePaginator
    {
        $query = QueryBuilder::for(ActualStock::class)
            ->select([
                'actual_stocks.id as id',
                'actual_stocks.batch as batch_code',
                'actual_stocks.quantity as quantity',
                'materials.period_id as period_id',
                'materials.code as material_code',
                'materials.description as material_description',
                'materials.uom as uom',
                'materials.mtyp as mtyp',
                'users.name as creator_name',
                'sub_areas.id as sub_area_id',
                'sub_areas.name as sub_area_name',
                'areas.name as area_name',
                'areas.sloc as sloc'
            ])
            ->leftJoin('sub_areas', 'sub_areas.id', '=', 'actual_stocks.sub_area_id')
            ->leftJoin('materials', 'materials.id', '=', 'actual_stocks.material_id')
            ->leftJoin('users', 'users.id', '=', 'actual_stocks.user_id')
            ->leftJoin('areas', 'areas.id', '=', 'sub_areas.area_id')
            ->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
            ->whereNull(['actual_stocks.deleted_at', 'sub_areas.deleted_at', 'areas.deleted_at', 'materials.deleted_at', 'users.deleted_at', 'periods.deleted_at']);
        if (!is_null($area)) {
            $query = $query->where('areas.id', $area->id);
        }
        if (!is_null($period)) {
            $query = $query->where('periods.id', $period->id);
        }
        if ($ownedByCurrentUser) {
            $query = $query->where('users.id', auth()->user()?->id ?? 0);
        }
        return $query->defaultSort('materials.code')
            ->allowedFilters(InertiaHelper::filterBy([
                'actual_stocks.batch',
                'actual_stocks.quantity',
                'materials.code',
                'materials.description',
                'materials.uom',
                'materials.mtyp',
                'users.name',
                'sub_areas.name'
            ]))
            ->allowedSorts([
                'batch_code',
                'quantity',
                'material_code',
                'material_description',
                'uom',
                'mtyp',
                'creator_name',
                'sub_area_name'
            ])
            ->paginate()
            ->withQueryString();
    }

    /**
     * @param InertiaTable $table
     * @param bool $ownedByCurrentUser
     * @return InertiaTable
     */
    public function tableMeta(InertiaTable $table, bool $ownedByCurrentUser = false): InertiaTable
    {
        $searchRows = [
            'materials.code' => 'Kode Material',
            'actual_stocks.batch' => 'Kode Batch',
            'materials.description' => 'Deskripsi Material',
            'actual_stocks.quantity' => 'Kuantitas',
            'materials.uom' => 'UoM',
            'materials.mtyp' => 'MType',
            'sub_areas.name' => 'Sub Area'
        ];
        $columns = [
            'material_code' => 'Kode Material',
            'batch_code' => 'Kode Batch',
            'material_description' => 'Deskripsi Material',
            'quantity' => 'Kuantitas',
            'uom' => 'UoM',
            'mtyp' => 'MType',
            'sub_area_name' => 'Sub Area',
            'action' => 'Aksi'
        ];
        if (!$ownedByCurrentUser) {
            $searchRows['users.name'] = 'Pembuat';
            $columns['creator_name'] = 'Pembuat';
        }
        return $table->addSearchRows($searchRows)->addColumns($columns);
    }

    /**
     * @param Request $request
     * @throws ValidationException
     */
    public function store(Request $request): void
    {
        $request->sku === true ? $this->storeBySkuCode($request) : $this->storeByMaterialCode($request);
    }

    /**
     * @param Request $request
     * @throws ValidationException
     */
    private function storeByMaterialCode(Request $request): void
    {
        $this->validate($request, [
            'sub_area.id' => ['required', 'integer', Rule::exists('sub_areas', 'id')->whereNull('deleted_at')],
            'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
            'material_code' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->where('period_id', $request->period)->whereNull('deleted_at')],
            'batch_code' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0']
        ], attributes: [
            'sub_area.id' => 'Sub Area',
            'period' => 'Periode',
            'material_code' => 'Kode Material',
            'batch_code' => 'Kode Batch',
            'quantity' => 'Kuantitas'
        ]);
        ActualStock::create([
            'sub_area_id' => (int)$request['sub_area.id'],
            'material_id' => Material::whereRaw('lower(code) = lower(?)', $request->material_code)->where('period_id', $request->period)->whereNull('deleted_at')->first()?->id ?? 0,
            'user_id' => auth()->user()?->id ?? 0,
            'batch' => Str::upper($request->batch_code),
            'quantity' => $request->quantity
        ]);
        auth()->user()?->notify(new DataStored('Actual Stock', Str::upper($request->material_code)));
    }

    /**
     * @param Request $request
     * @throws ValidationException
     */
    private function storeBySkuCode(Request $request): void
    {
        $this->validate($request, [
            'sub_area.id' => ['required', 'integer', Rule::exists('sub_areas', 'id')->whereNull('deleted_at')],
            'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
            'product_code' => ['required', 'string', 'max:255', Rule::exists('products', 'code')->where('period_id', $request->period)->whereNull('deleted_at')],
            'batch_code' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:0']
        ], attributes: [
            'sub_area.id' => 'Sub Area',
            'period' => 'Periode',
            'product_code' => 'Kode SKU',
            'batch_code' => 'Kode Batch',
            'quantity' => 'Kuantitas'
        ]);
        $product = Product::whereRaw('lower(code) = lower(?)', $request->product_code)->where('period_id', $request->period)->whereNull('deleted_at')->first();
        $product?->convertAsActualStock($request);
        auth()->user()?->notify(new DataStored('Actual Stock', Str::upper($request->material_code)));
    }

    /**
     * @param Request $request
     * @param ActualStock $actual
     * @throws Throwable
     */
    public function update(Request $request, ActualStock $actual): void
    {
        $this->validate($request, [
            'sub_area.id' => ['required', 'integer', Rule::exists('sub_areas', 'id')->whereNull('deleted_at')],
            'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
            'material_code' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->where('period_id', $request->period)->whereNull('deleted_at')],
            'batch_code' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0']
        ], attributes: [
            'sub_area.id' => 'Sub Area',
            'period' => 'Periode',
            'material_code' => 'Kode Material',
            'batch_code' => 'Kode Batch',
            'quantity' => 'Kuantitas'
        ]);
        $actual->updateOrFail([
            'sub_area_id' => (int)$request['sub_area.id'],
            'material_id' => Material::whereRaw('lower(code) = lower(?)', $request->material_code)->where('period_id', $request->period)->whereNull('deleted_at')->first()?->id ?? 0,
            'batch' => Str::upper($request->batch_code),
            'quantity' => $request->quantity
        ]);
        $actual->save();
        auth()->user()?->notify(new DataUpdated('Actual Stock', Str::upper($request->material_code)));
    }

    /**
     * @param ActualStock $actual
     * @throws Throwable
     */
    public function destroy(ActualStock $actual): void
    {
        $data = $actual->load('material')->material->code;
        $actual->deleteOrFail();
        auth()->user()?->notify(new DataDestroyed('Actual Stock', $data));
    }

    /**
     * @param Request $request
     * @throws Throwable
     */
    public function import(Request $request): void
    {
        $this->validate($request, [
            'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
            'file' => ['required', 'mimes:xlsx,csv', 'max:' . MediaHelper::SPREADSHEET_MAX_SIZE]
        ], attributes: [
            'period' => 'Periode',
            'file' => 'File'
        ]);
        JobHelper::limitOnce();
        Excel::import(new ActualStocksImport(
            Period::where('id', (int)$request->period)->first(),
            auth()->user()
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
            $this,
            $this->areaService->resolve($request),
            $this->periodService->resolve($request)
        ), new ActualStock);
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return 'https://docs.google.com/spreadsheets/d/1YNy7flR0ALyR0GAdPfVKVYlXiEWvJobaRrVnKdPrW6M/edit?usp=sharing';
    }

    public function collection(?Area $area, ?Period $period): Collection
    {
        $query = ActualStock::leftJoin('sub_areas', 'sub_areas.id', '=', 'actual_stocks.sub_area_id')
            ->leftJoin('materials', 'materials.id', '=', 'actual_stocks.material_id')
            ->leftJoin('areas', 'areas.id', '=', 'sub_areas.area_id')
            ->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
            ->whereNull(['actual_stocks.deleted_at', 'sub_areas.deleted_at', 'areas.deleted_at', 'materials.deleted_at', 'periods.deleted_at']);
        if (!is_null($area)) {
            $query = $query->where('areas.id', $area->id);
        }
        if (!is_null($period)) {
            $query = $query->where('periods.id', $period->id);
        }
        return $query->orderBy('materials.code')->get()->load(['subArea', 'material']);
    }
}
