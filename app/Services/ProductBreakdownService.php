<?php

namespace App\Services;

use App\Exports\ProductBreakdownsExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\JobHelper;
use App\Http\Helper\MediaHelper;
use App\Imports\ProductBreakdownsImport;
use App\Models\Area;
use App\Models\Period;
use App\Models\Product;
use App\Models\ProductBreakdown;
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

class ProductBreakdownService
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
		$query = QueryBuilder::for(ProductBreakdown::class)
			->select([
				'product_breakdowns.id as id',
				'product_breakdowns.batch as batch_code',
				'products.period_id as period_id',
				'products.code as product_code',
				'products.description as product_description',
				'materials.code as material_code',
				'materials.description as material_description',
				'users.name as creator_name',
				'sub_areas.id as sub_area_id',
				'sub_areas.name as sub_area_name',
				'areas.name as area_name',
				'areas.sloc as sloc',
				DB::raw('if(product_breakdowns.actual_stock_id is null, 0, 1) as converted')
			])
			->leftJoin('users', 'users.id', '=', 'product_breakdowns.user_id')
			->leftJoin('sub_areas', 'sub_areas.id', '=', 'product_breakdowns.sub_area_id')
			->leftJoin('areas', 'areas.id', '=', 'sub_areas.area_id')
			->leftJoin('product_materials', 'product_materials.id', '=', 'product_breakdowns.product_material_id')
			->leftJoin('products', 'products.id', '=', 'product_materials.product_id')
			->leftJoin('materials', 'materials.id', '=', 'product_materials.material_id')
			->leftJoin('periods', 'periods.id', '=', 'products.period_id')
			->whereNull(['sub_areas.deleted_at', 'areas.deleted_at', 'product_materials.deleted_at', 'products.deleted_at', 'materials.deleted_at', 'periods.deleted_at', 'users.deleted_at']);
		if (!is_null($area)) {
			$query = $query->where('areas.id', $area->id);
		}
		if (!is_null($period)) {
			$query = $query->where('periods.id', $period->id);
		}
		return $query->defaultSort('products.code')
			->allowedFilters(InertiaHelper::filterBy([
				'product_breakdowns.batch',
				'products.code',
				'products.description',
				'materials.code',
				'materials.description',
				'sub_areas.name',
				'users.name'
			]))
			->allowedSorts([
				'batch_code',
				'product_code',
				'product_description',
				'material_code',
				'material_description',
				'sub_area_name',
				'creator_name'
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
			'product_breakdowns.batch' => 'Kode Batch',
			'products.code' => 'Kode SKU',
			'products.description' => 'Deskripsi Produk',
			'materials.code' => 'Kode Material',
			'materials.description' => 'Deskripsi Material',
			'sub_areas.name' => 'Sub Area',
			'users.name' => 'Pembuat'
		])->addColumns([
			'batch_code' => 'Kode Batch',
			'product_code' => 'Kode SKU',
			'product_description' => 'Deskripsi Produk',
			'material_code' => 'Kode Material',
			'material_description' => 'Deskripsi Material',
			'sub_area_name' => 'Sub Area',
			'creator_name' => 'Pembuat',
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
			'sub_area.id' => ['required', 'integer', Rule::exists('sub_areas', 'id')->whereNull('deleted_at')],
			'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
			'product_code' => ['required', 'string', 'max:255', Rule::exists('products', 'code')->where('period_id', $request->period)->whereNull('deleted_at')],
			'quantity' => ['required', 'integer', 'min:0']
		], attributes: [
			'sub_area.id' => 'Sub Area',
			'period' => 'Periode',
			'product_code' => 'Kode SKU',
			'quantity' => 'Kuantitas'
		]);
		$product = Product::whereRaw('lower(code) = lower(?)', $request->product_code)->where('period_id', $request->period)->whereNull('deleted_at')->first();
		$product?->performBreakdown($request);
		auth()->user()?->notify(new DataStored('FG Material Breakdown', Str::upper($request->product_code)));
	}

	/**
	 * @param Request $request
	 * @param ProductBreakdown $breakdown
	 * @throws Throwable
	 */
	public function update(Request $request, ProductBreakdown $breakdown): void
	{
		$breakdown->load('productMaterial');
		$product = $breakdown->productMaterial->load('product')->product;
		$material = $breakdown->productMaterial->load('material')->material;
		$this->validate($request, [
			'batch_code' => ['required', 'string', 'max:255', Rule::exists('batches', 'code')->where('material_id', $material->id)->whereNull('deleted_at')]
		], attributes: [
			'batch_code' => 'Kode Batch'
		]);
		$breakdown->convertAsActualStock(Str::upper($request->batch_code));
		auth()->user()?->notify(new DataUpdated('FG Material Breakdown', Str::upper($product->code)));
	}

	/**
	 * @param ProductBreakdown $breakdown
	 * @throws Throwable
	 */
	public function destroy(ProductBreakdown $breakdown): void
	{
		$data = $breakdown->load('productMaterial')->productMaterial->load('product')->product->code;
		$breakdown->deleteOrFail();
		auth()->user()?->notify(new DataDestroyed('FG Material Breakdown', $data));
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
		Excel::import(new ProductBreakdownsImport(
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
		return MediaHelper::exportSpreadsheet(new ProductBreakdownsExport(
			$this,
			$this->areaService->resolve($request),
			$this->periodService->resolve($request)
		), 'fg_material_breakdowns');
	}

	/**
	 * @return string
	 */
	public function template(): string
	{
		return 'https://docs.google.com/spreadsheets/d/1NzrkuCMDJzMzNypYoZ-UOs_EFtZLy0njm_gXyBnz7Z0/edit?usp=sharing';
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
	 * @param Area|null $area
	 * @param Period|null $period
	 * @return Collection
	 */
	public function collection(?Area $area = null, ?Period $period = null): Collection
	{
		$query = ProductBreakdown::select([
			'product_breakdowns.id as id',
			'product_breakdowns.batch as batch_code',
			'products.period_id as period_id',
			'products.code as product_code',
			'products.description as product_description',
			'materials.code as material_code',
			'materials.description as material_description',
			'users.name as creator_name',
			'sub_areas.id as sub_area_id',
			'sub_areas.name as sub_area_name',
			'areas.name as area_name',
			'areas.sloc as sloc',
		])
			->orderBy('products.code')
			->leftJoin('users', 'users.id', '=', 'product_breakdowns.user_id')
			->leftJoin('sub_areas', 'sub_areas.id', '=', 'product_breakdowns.sub_area_id')
			->leftJoin('areas', 'areas.id', '=', 'sub_areas.area_id')
			->leftJoin('product_materials', 'product_materials.id', '=', 'product_breakdowns.product_material_id')
			->leftJoin('products', 'products.id', '=', 'product_materials.product_id')
			->leftJoin('materials', 'materials.id', '=', 'product_materials.material_id')
			->leftJoin('periods', 'periods.id', '=', 'products.period_id')
			->whereNull(['sub_areas.deleted_at', 'areas.deleted_at', 'product_materials.deleted_at', 'products.deleted_at', 'materials.deleted_at', 'periods.deleted_at']);
		if (!is_null($area)) {
			$query = $query->where('areas.id', $area->id);
		}
		if (!is_null($period)) {
			$query = $query->where('periods.id', $period->id);
		}
		return $query->get();
	}
}
