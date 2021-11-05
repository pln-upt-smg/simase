<?php

namespace App\Services;

use App\Exports\ProductMaterialsExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\MediaHelper;
use App\Imports\ProductMaterialsImport;
use App\Models\Area;
use App\Models\Material;
use App\Models\Period;
use App\Models\Product;
use App\Models\ProductMaterial;
use App\Notifications\DataDestroyed;
use App\Notifications\DataImported;
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

class ProductMaterialService
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
        $query = QueryBuilder::for(ProductMaterial::class)
            ->select([
                'product_materials.id as id',
                'product_materials.material_uom as material_uom',
                'product_materials.material_quantity as material_quantity',
                'product_materials.product_quantity as product_quantity',
                'products.area_id as area_id',
                'products.period_id as period_id',
                'products.code as product_code',
                'products.description as product_description',
                'products.uom as product_uom',
                'materials.code as material_code',
                'materials.description as material_description'
            ])
            ->leftJoin('products', 'products.id', '=', 'product_materials.product_id')
            ->leftJoin('materials', 'materials.id', '=', 'product_materials.material_id')
            ->whereNull(['product_materials.deleted_at', 'products.deleted_at', 'materials.deleted_at']);
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
                'product_materials.material_uom',
                'product_materials.material_quantity',
                'product_materials.product_quantity',
                'products.code',
                'products.description',
                'products.uom',
                'materials.code',
                'materials.description'
            ]))
            ->allowedSorts([
                'material_uom',
                'material_quantity',
                'product_quantity',
                'product_code',
                'product_description',
                'product_uom',
                'material_code',
                'material_description'
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
            'product_materials.product_quantity' => 'Kuantitas Produk',
            'materials.code' => 'Kode Material',
            'materials.description' => 'Deskripsi Material',
            'product_materials.material_uom' => 'UoM Material',
            'product_materials.material_quantity' => 'Kuantitas Material'
        ])->addColumns([
            'product_code' => 'Kode SKU',
            'product_description' => 'Deskripsi Produk',
            'product_quantity' => 'Kuantitas Produk',
            'material_code' => 'Kode Material',
            'material_description' => 'Deskripsi Material',
            'material_uom' => 'UoM Material',
            'material_quantity' => 'Kuantitas Material',
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
            'product_code' => ['required', 'string', 'max:255', Rule::exists('products', 'code')->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at')],
            'product_quantity' => ['required', 'integer', 'min:0'],
            'material_code' => ['required', 'string', 'max:255',
                Rule::exists('materials', 'code')->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at'),
                Rule::unique('product_materials', 'material_id')
                    ->where('product_id', Product::whereRaw('lower(code) = lower(?)', $request->product_code)->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at')->first()?->id ?? 0)
                    ->whereNull('deleted_at')
            ],
            'material_quantity' => ['required', 'integer', 'min:0'],
            'material_uom' => ['required', 'string', 'max:255']
        ], attributes: [
            'area' => 'Area',
            'period' => 'Periode',
            'product_code' => 'Kode SKU',
            'product_quantity' => 'Kuantitas Produk',
            'material_code' => 'Kode Material',
            'material_quantity' => 'Kuantitas Material',
            'material_uom' => 'UoM Material'
        ]);
        ProductMaterial::create([
            'product_id' => Product::whereRaw('lower(code) = lower(?)', $request->product_code)->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at')->first()?->id ?? 0,
            'material_id' => Material::whereRaw('lower(code) = lower(?)', $request->material_code)->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at')->first()?->id ?? 0,
            'material_uom' => Str::upper($request->material_uom),
            'material_quantity' => (int)$request->material_quantity,
            'product_quantity' => (int)$request->product_quantity
        ]);
        auth()->user()?->notify(new DataStored('Product Material', Str::upper($request->product_code)));
    }

    /**
     * @param Request $request
     * @param ProductMaterial $material
     * @throws Throwable
     */
    public function update(Request $request, ProductMaterial $material): void
    {
        $this->validate($request, [
            'area' => ['required', 'integer', Rule::exists('areas', 'id')->whereNull('deleted_at')],
            'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
            'product_code' => ['required', 'string', 'max:255', Rule::exists('products', 'code')->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at')],
            'product_quantity' => ['required', 'integer', 'min:0'],
            'material_code' => ['required', 'string', 'max:255',
                Rule::exists('materials', 'code')->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at'),
                Rule::unique('product_materials', 'material_id')
                    ->where('product_id', Product::whereRaw('lower(code) = lower(?)', $request->product_code)->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at')->first()?->id ?? 0)
                    ->whereNull('deleted_at')
                    ->ignore($material->id)
            ],
            'material_quantity' => ['required', 'integer', 'min:0'],
            'material_uom' => ['required', 'string', 'max:255']
        ], attributes: [
            'area' => 'Area',
            'period' => 'Periode',
            'product_code' => 'Kode SKU',
            'product_quantity' => 'Kuantitas Produk',
            'material_code' => 'Kode Material',
            'material_quantity' => 'Kuantitas Material',
            'material_uom' => 'UoM Material'
        ]);
        $material->updateOrFail([
            'product_id' => Product::whereRaw('lower(code) = lower(?)', $request->product_code)->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at')->first()?->id ?? 0,
            'material_id' => Material::whereRaw('lower(code) = lower(?)', $request->material_code)->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at')->first()?->id ?? 0,
            'material_uom' => Str::upper($request->material_uom),
            'material_quantity' => (int)$request->material_quantity,
            'product_quantity' => (int)$request->product_quantity
        ]);
        $material->save();
        auth()->user()?->notify(new DataUpdated('Product Material', Str::upper($request->product_code)));
    }

    /**
     * @param ProductMaterial $material
     * @throws Throwable
     */
    public function destroy(ProductMaterial $material): void
    {
        $data = $material->load('product')->product->code;
        $material->deleteOrFail();
        auth()->user()?->notify(new DataDestroyed('Product Material', $data));
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
        $import = new ProductMaterialsImport(Period::where('id', (int)$request->period)->first());
        Excel::import($import, $request->file('file'));
        auth()->user()?->notify(new DataImported('Product Material', $import->getRowCount()));
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(Request $request): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(new ProductMaterialsExport(
            $this->areaService->resolve($request),
            $this->periodService->resolve($request),
            $this
        ), new ProductMaterial);
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return 'https://docs.google.com/spreadsheets/d/1FEG6LsAx-FU015JYA95jletTaKUS8ZMCVMLH72KW9UE/edit?usp=sharing';
    }

    /**
     * @param Area|null $area
     * @param Period|null $period
     * @return Collection
     */
    public function collection(?Area $area, ?Period $period): Collection
    {
        $query = ProductMaterial::leftJoin('products', 'products.id', '=', 'product_materials.product_id')
            ->leftJoin('materials', 'materials.id', '=', 'product_materials.material_id')
            ->whereNull(['product_materials.deleted_at', 'products.deleted_at', 'materials.deleted_at']);
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
        return $query->orderBy('products.code')->get()->load(['product', 'product.area', 'material']);
    }
}
