<?php

namespace App\Services;

use App\Exports\ProductsExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\JobHelper;
use App\Http\Helper\MediaHelper;
use App\Imports\ProductsImport;
use App\Models\Period;
use App\Models\Product;
use App\Notifications\DataDestroyed;
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
     * @var PeriodService
     */
    private PeriodService $periodService;

    /**
     * @param PeriodService $periodService
     */
    public function __construct(PeriodService $periodService)
    {
        $this->periodService = $periodService;
    }

    /**
     * @param Period|null $period
     * @return LengthAwarePaginator
     */
    public function tableData(?Period $period): LengthAwarePaginator
    {
        $query = QueryBuilder::for(Product::class)
            ->select([
                'products.id as id',
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
            ->leftJoin('periods', 'periods.id', '=', 'products.period_id')
            ->whereNull(['products.deleted_at', 'periods.deleted_at']);
        if (!is_null($period)) {
            $query = $query->where('periods.id', $period->id);
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
            'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:255', Rule::unique('products', 'code')->where('period_id', $request->period)->whereNull('deleted_at')],
            'description' => ['required', 'string', 'max:255'],
            'uom' => ['required', 'string', 'max:255'],
            'mtyp' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'per' => ['required', 'integer', 'min:0']
        ], attributes: [
            'period' => 'Periode',
            'code' => 'Kode SKU',
            'description' => 'Deskripsi Produk',
            'uom' => 'UoM',
            'mtyp' => 'MType',
            'price' => 'Harga',
            'per' => 'Per'
        ]);
        Product::create([
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
            'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:255', Rule::unique('products', 'code')->where('period_id', $request->period)->ignore($product->id)->whereNull('deleted_at')],
            'description' => ['required', 'string', 'max:255'],
            'uom' => ['required', 'string', 'max:255'],
            'mtyp' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'per' => ['required', 'integer', 'min:0']
        ], attributes: [
            'period' => 'Periode',
            'code' => 'Kode SKU',
            'description' => 'Deskripsi Produk',
            'uom' => 'UoM',
            'mtyp' => 'MType',
            'price' => 'Harga',
            'per' => 'Per'
        ]);
        $product->updateOrFail([
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
            'file' => ['required', 'mimes:xlsx,csv', 'max:' . MediaHelper::SPREADSHEET_MAX_SIZE]
        ], attributes: [
            'period' => 'Periode',
            'file' => 'File'
        ]);
        JobHelper::limitOnce();
        Excel::import(new ProductsImport(
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
        return MediaHelper::exportSpreadsheet(new ProductsExport(
            $this,
            $this->periodService->resolve($request)
        ), 'fg_masters');
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return 'https://docs.google.com/spreadsheets/d/1sG-gvu4fANyxE_g05BC4WU4navY8w0rp95crvXZf6Cw/edit?usp=sharing';
    }

    /**
     * @param Request $request
     * @return Product|null
     */
    public function single(Request $request): ?Product
    {
        return $this->collection($request)->first();
    }

    /**
     * @param Request|null $request
     * @param Period|null $period
     * @return Collection
     */
    public function collection(?Request $request = null, ?Period $period = null): Collection
    {
        $query = Product::leftJoin('periods', 'periods.id', '=', 'products.period_id')
            ->orderBy('products.code')
            ->whereNull(['products.deleted_at', 'periods.deleted_at']);
        if (!is_null($request)) {
            $query = $query->where('periods.id', $this->periodService->resolve($request)?->id ?? 0)
                ->whereRaw('lower(products.code) like ?', '%' . Str::lower($request->query('q') ?? '') . '%');
        } else if (!is_null($period)) {
            $query = $query->where('periods.id', $period->id);
        }
        return $query->get();
    }
}
