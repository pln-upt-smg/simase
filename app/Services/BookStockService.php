<?php

namespace App\Services;

use App\Exports\BookStocksExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\JobHelper;
use App\Http\Helper\MediaHelper;
use App\Imports\BookStocksImport;
use App\Models\Area;
use App\Models\BookStock;
use App\Models\Material;
use App\Models\Period;
use App\Notifications\DataDestroyed;
use App\Notifications\DataStored;
use App\Notifications\DataTruncated;
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

class BookStockService
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
		$query = QueryBuilder::for(BookStock::class)
			->select([
				'book_stocks.id as id',
				'book_stocks.area_id as area_id',
				'book_stocks.batch as batch_code',
				'book_stocks.quantity as quantity',
				'book_stocks.plnt as plnt',
				'book_stocks.qualinsp as qualinsp',
				'book_stocks.unrestricted as unrestricted',
				'materials.period_id as period_id',
				'materials.code as material_code',
				'materials.description as material_description',
				'materials.uom as uom',
				'materials.mtyp as mtyp',
				'areas.sloc as sloc'
			])
			->leftJoin('areas', 'areas.id', '=', 'book_stocks.area_id')
			->leftJoin('materials', 'materials.id', '=', 'book_stocks.material_id')
			->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
			->whereNull(['book_stocks.deleted_at', 'areas.deleted_at', 'materials.deleted_at', 'periods.deleted_at']);
		if (!is_null($area)) {
			$query = $query->where('areas.id', $area->id);
		}
		if (!is_null($period)) {
			$query = $query->where('periods.id', $period->id);
		}
		return $query->defaultSort('materials.code')
			->allowedFilters(InertiaHelper::filterBy([
				'book_stocks.batch',
				'book_stocks.quantity',
				'book_stocks.plnt',
				'book_stocks.qualinsp',
				'book_stocks.unrestricted',
				'materials.code',
				'materials.description',
				'materials.uom',
				'materials.mtyp',
				'areas.sloc'
			]))
			->allowedSorts([
				'batch_code',
				'quantity',
				'plnt',
				'sloc',
				'qualinsp',
				'unrestricted',
				'material_code',
				'material_description',
				'uom',
				'mtyp'
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
			'book_stocks.batch' => 'Kode Batch',
			'book_stocks.plnt' => 'Plnt',
			'areas.sloc' => 'SLoc',
			'book_stocks.qualinsp' => 'QualInsp',
			'book_stocks.unrestricted' => 'Unrestricted',
			'book_stocks.quantity' => 'Kuantitas'
		])->addColumns([
			'material_code' => 'Kode Material',
			'material_description' => 'Deskripsi Material',
			'uom' => 'UoM',
			'mtyp' => 'MType',
			'batch_code' => 'Kode Batch',
			'plnt' => 'Plnt',
			'sloc' => 'SLoc',
			'qualinsp' => 'QualInsp',
			'unrestricted' => 'Unrestricted',
			'quantity' => 'Kuantitas',
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
			'material_code' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->where('period_id', $request->period)->whereNull('deleted_at')],
			'batch_code' => ['required', 'string', 'max:255'],
			'plnt' => ['required', 'integer', 'min:0'],
			'qualinsp' => ['required', 'integer', 'min:0'],
			'unrestricted' => ['required', 'numeric'],
			'quantity' => ['required', 'numeric', 'min:0']
		], attributes: [
			'area' => 'Area',
			'period' => 'Periode',
			'material_code' => 'Kode Material',
			'batch_code' => 'Kode Batch',
			'plnt' => 'Plnt',
			'qualinsp' => 'QualInsp',
			'unrestricted' => 'Unrestricted',
			'quantity' => 'Kuantitas'
		]);
		BookStock::create([
			'area_id' => (int)$request->area,
			'material_id' => Material::whereRaw('lower(code) = lower(?)', $request->material_code)->where('period_id', $request->period)->whereNull('deleted_at')->first()?->id ?? 0,
			'batch' => Str::upper($request->batch_code),
			'plnt' => (int)$request->plnt,
			'qualinsp' => (int)$request->qualinsp,
			'unrestricted' => $request->unrestricted,
			'quantity' => $request->quantity
		]);
		auth()->user()?->notify(new DataStored('Book Stock', Str::upper($request->material_code)));
	}

	/**
	 * @param Request $request
	 * @param BookStock $book
	 * @throws Throwable
	 */
	public function update(Request $request, BookStock $book): void
	{
		$this->validate($request, [
			'area' => ['required', 'integer', Rule::exists('areas', 'id')->whereNull('deleted_at')],
			'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
			'material_code' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->where('period_id', $request->period)->whereNull('deleted_at')],
			'batch_code' => ['required', 'string', 'max:255'],
			'plnt' => ['required', 'integer', 'min:0'],
			'qualinsp' => ['required', 'integer', 'min:0'],
			'unrestricted' => ['required', 'numeric'],
			'quantity' => ['required', 'numeric', 'min:0']
		], attributes: [
			'area' => 'Area',
			'period' => 'Periode',
			'material_code' => 'Kode Material',
			'batch_code' => 'Kode Batch',
			'plnt' => 'Plnt',
			'qualinsp' => 'QualInsp',
			'unrestricted' => 'Unrestricted',
			'quantity' => 'Kuantitas'
		]);
		$book->updateOrFail([
			'area_id' => (int)$request->area,
			'material_id' => Material::whereRaw('lower(code) = lower(?)', $request->material_code)->where('period_id', $request->period)->whereNull('deleted_at')->first()?->id ?? 0,
			'batch' => Str::upper($request->batch_code),
			'plnt' => (int)$request->plnt,
			'qualinsp' => (int)$request->qualinsp,
			'unrestricted' => $request->unrestricted,
			'quantity' => $request->quantity
		]);
		$book->save();
		auth()->user()?->notify(new DataUpdated('Book Stock', Str::upper($request->material_code)));
	}

	/**
	 * @param BookStock $book
	 * @throws Throwable
	 */
	public function destroy(BookStock $book): void
	{
		$data = $book->load('material')->material->code;
		$book->deleteOrFail();
		auth()->user()?->notify(new DataDestroyed('Book Stock', $data));
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
		Excel::import(new BookStocksImport(
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
		return MediaHelper::exportSpreadsheet(new BookStocksExport(
			$this,
			$this->areaService->resolve($request),
			$this->periodService->resolve($request)
		), new BookStock);
	}

	/**
	 * @param Area|null $area
	 * @param Period|null $period
	 */
	public function truncate(?Area $area, ?Period $period): void
	{
		BookStock::whereNull('deleted_at')
			->when(!is_null($area), function ($query, $area) {
				$query->where('book_stocks.area_id', $area);
			})
			->when(!is_null($period), function ($query, $period) {
				$query->leftJoin('materials', 'materials.id', '=', 'book_stocks.material_id')
					->where('materials.period_id', $period);
			})
			->delete();
		auth()->user()?->notify(new DataTruncated('Book Stock'));
	}

	/**
	 * @return string
	 */
	public function template(): string
	{
		return 'https://docs.google.com/spreadsheets/d/1v3iWj5ZJ-MUwNkVZI6VEDC_Cf3RufSnRREuYFewXyYg/edit?usp=sharing';
	}

	/**
	 * @param Area|null $area
	 * @param Period|null $period
	 * @return Collection
	 */
	public function collection(?Area $area, ?Period $period): Collection
	{
		$query = BookStock::leftJoin('areas', 'areas.id', '=', 'book_stocks.area_id')
			->leftJoin('materials', 'materials.id', '=', 'book_stocks.material_id')
			->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
			->whereNull(['book_stocks.deleted_at', 'areas.deleted_at', 'materials.deleted_at', 'periods.deleted_at']);
		if (!is_null($area)) {
			$query = $query->where('areas.id', $area->id);
		}
		if (!is_null($period)) {
			$query = $query->where('periods.id', $period->id);
		}
		return $query->orderBy('materials.code')->get()->load(['material', 'area']);
	}
}
