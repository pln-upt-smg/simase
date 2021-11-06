<?php

namespace App\Services;

use App\Exports\ProductsExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\MediaHelper;
use App\Http\Helper\SystemHelper;
use App\Imports\ProductsImport;
use App\Models\Area;
use App\Models\Period;
use App\Models\Product;
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

class ProductService
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
        $query = QueryBuilder::for(Product::class)
            ->select([
                'products.id as id',
                'products.area_id as area_id',
                'products.period_id as period_id',
                'products.code as code',
                'products.description as description',
                'products.uom as uom',
                'products.mtyp as mtyp',
                'products.crcy as crcy',
                'products.price as price',
                'products.per as per',
                DB::raw('date_format(products.updated_at, "%d %b %Y") as update_date')
            ])
            ->whereNull('products.deleted_at');
        if (!is_null($area)) {
            $query = $query->leftJoin('areas', 'areas.id', '=', 'products.area_id')
                ->where('areas.id', $area->id)
                ->whereNull('areas.deleted_at');
        }
        if (!is_null($period)) {
            $query = $query->leftJoin('periods', 'periods.id', '=', 'products.period_id')
                ->where('periods.id', $period->id)
                ->whereNull('periods.deleted_at');
        }
        return $query->defaultSort('products.code')
            ->allowedFilters(InertiaHelper::filterBy([
                'products.code',
                'products.description',
                'products.uom',
                'products.mtyp',
                'products.crcy',
                'products.price',
                'products.per'
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
            'products.code' => 'Kode SKU',
            'products.description' => 'Deskripsi Produk',
            'products.uom' => 'UoM',
            'products.mtyp' => 'MType',
            'products.crcy' => 'Currency',
            'products.price' => 'Harga',
            'products.per' => 'Per'
        ])->addColumns([
            'code' => 'Kode SKU',
            'description' => 'Deskripsi Produk',
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
            'code' => ['required', 'string', 'max:255', Rule::unique('products', 'code')->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at')],
            'description' => ['required', 'string', 'max:255'],
            'uom' => ['required', 'string', 'max:255'],
            'mtyp' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'per' => ['required', 'integer', 'min:0']
        ], attributes: [
            'area' => 'Area',
            'period' => 'Periode',
            'code' => 'Kode SKU',
            'description' => 'Deskripsi Produk',
            'uom' => 'UoM',
            'mtyp' => 'MType',
            'price' => 'Harga',
            'per' => 'Per'
        ]);
        Product::create([
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
        auth()->user()?->notify(new DataStored('Product', Str::upper($request->code)));
    }

    /**
     * @param Request $request
     * @param Product $product
     * @throws Throwable
     */
    public function update(Request $request, Product $product): void
    {
        $this->validate($request, [
            'area' => ['required', 'integer', Rule::exists('areas', 'id')->whereNull('deleted_at')],
            'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:255', Rule::unique('products', 'code')->where('area_id', $request->area)->where('period_id', $request->period)->ignore($product->id)->whereNull('deleted_at')],
            'description' => ['required', 'string', 'max:255'],
            'uom' => ['required', 'string', 'max:255'],
            'mtyp' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'per' => ['required', 'integer', 'min:0']
        ], attributes: [
            'area' => 'Area',
            'period' => 'Periode',
            'code' => 'Kode SKU',
            'description' => 'Deskripsi Produk',
            'uom' => 'UoM',
            'mtyp' => 'MType',
            'price' => 'Harga',
            'per' => 'Per'
        ]);
        $product->updateOrFail([
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
        $product->save();
        auth()->user()?->notify(new DataUpdated('Product', Str::upper($request->code)));
    }

    /**
     * @param Product $product
     * @throws Throwable
     */
    public function destroy(Product $product): void
    {
        $data = $product->code;
        $product->deleteOrFail();
        auth()->user()?->notify(new DataDestroyed('Product', $data));
    }

    /**
     * @param Request $request
     * @throws Throwable
     */
    public function import(Request $request): void
    {
        $this->validate($request, [
            'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
            'file' => ['required', 'mimes:xls,xlsx,csv', 'max:' . MediaHelper::SPREADSHEET_MAX_SIZE]
        ], attributes: [
            'period' => 'Periode',
            'file' => 'File'
        ]);
        SystemHelper::allowLongerExecutionTimeLimit();
        $import = new ProductsImport(Period::where('id', (int)$request->period)->first());
        Excel::import($import, $request->file('file'));
        auth()->user()?->notify(new DataImported('Product', $import->getRowCount()));
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(Request $request): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(new ProductsExport(
            $this->areaService->resolve($request),
            $this->periodService->resolve($request),
            $this
        ), new Product);
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return 'https://docs.google.com/spreadsheets/d/1sG-gvu4fANyxE_g05BC4WU4navY8w0rp95crvXZf6Cw/edit?usp=sharing';
    }

    /**
     * @param Area|null $area
     * @param Period|null $period
     * @return Collection
     */
    public function collection(?Area $area, ?Period $period): Collection
    {
        $query = Product::whereNull('products.deleted_at');
        if (!is_null($area)) {
            $query = $query->leftJoin('areas', 'areas.id', '=', 'products.area_id')
                ->where('areas.id', $area->id)
                ->whereNull('areas.deleted_at');
        }
        if (!is_null($period)) {
            $query = $query->leftJoin('periods', 'periods.id', '=', 'products.period_id')
                ->where('periods.id', $period->id)
                ->whereNull('periods.deleted_at');
        }
        return $query->orderBy('products.code')->get()->load('area');
    }

    public function resolveProductCode(Request $request, bool $strict = true): ?Product
    {
        $area = $this->areaService->resolve($request);
        $period = $this->periodService->resolve($request);
        $query = Product::whereRaw('lower(products.code) = ?', Str::lower(trim($request->query('q') ?? '')))
            ->whereNull('products.deleted_at');
        if ($strict) {
            $query = $query->leftJoin('areas', 'areas.id', '=', 'products.area_id')
                ->leftJoin('periods', 'periods.id', '=', 'products.period_id')
                ->where('areas.id', $area?->id ?? 0)
                ->where('periods.id', $period?->id ?? 0)
                ->whereNull(['areas.deleted_at', 'periods.deleted_at']);
        } else {
            if (!is_null($area)) {
                $query = $query->leftJoin('areas', 'areas.id', '=', 'products.area_id')
                    ->where('areas.id', $area->id)
                    ->whereNull('areas.deleted_at');
            }
            if (!is_null($period)) {
                $query = $query->leftJoin('periods', 'periods.id', '=', 'products.period_id')
                    ->where('periods.id', $period->id)
                    ->whereNull('periods.deleted_at');
            }
        }
        return $query->first();
    }

    public function productCodeJsonCollection(Request $request, bool $strict = true): Collection
    {
        $area = $this->areaService->resolve($request);
        $period = $this->periodService->resolve($request);
        $query = Product::distinct()
            ->select([
                'products.code as code',
                'products.description as description',
                'products.uom as uom'
            ])
            ->orderBy('products.code')
            ->whereNull('products.deleted_at')
            ->whereRaw('lower(products.code) like ?', '%' . Str::lower(trim($request->query('q') ?? '')) . '%');
        if ($strict) {
            $query = $query->leftJoin('areas', 'areas.id', '=', 'products.area_id')
                ->leftJoin('periods', 'periods.id', '=', 'products.period_id')
                ->where('areas.id', $area?->id ?? 0)
                ->where('periods.id', $period?->id ?? 0)
                ->whereNull(['areas.deleted_at', 'periods.deleted_at']);
        } else {
            if (!is_null($area)) {
                $query = $query->leftJoin('areas', 'areas.id', '=', 'products.area_id')
                    ->where('areas.id', $area->id)
                    ->whereNull('areas.deleted_at');
            }
            if (!is_null($period)) {
                $query = $query->leftJoin('periods', 'periods.id', '=', 'products.period_id')
                    ->where('periods.id', $period->id)
                    ->whereNull('periods.deleted_at');
            }
        }
        return $query->get();
    }
}
