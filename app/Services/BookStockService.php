<?php

namespace App\Services;

use App\Exports\BookStocksExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\MediaHelper;
use App\Imports\BookStocksImport;
use App\Models\Area;
use App\Models\BookStock;
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

class BookStockService
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
        $query = QueryBuilder::for(BookStock::class)
            ->select([
                'book_stocks.id as id',
                'book_stocks.batch as batch',
                'book_stocks.quantity as quantity',
                'book_stocks.plnt as plnt',
                'book_stocks.sloc as sloc',
                'book_stocks.qualinsp as qualinsp',
                'book_stocks.unrestricted as unrestricted',
                'materials.area_id as area_id',
                'materials.period_id as period_id',
                'materials.code as material_code',
                'materials.description as material_description',
                'materials.uom as uom',
                'materials.mtyp as mtyp'
            ])
            ->leftJoin('materials', 'materials.id', '=', 'book_stocks.material_id')
            ->whereNull('book_stocks.deleted_at')
            ->whereNull('materials.deleted_at');

        if (!is_null($area)) {
            $query = $query->where('materials.area_id', $area->id);
        }

        if (!is_null($period)) {
            $query = $query->where('materials.period_id', $period->id);
        }

        return $query->defaultSort('material_code')
            ->allowedSorts([
                'material_code',
                'material_description',
                'uom',
                'mtyp',
                'batch',
                'plnt',
                'sloc',
                'qualinsp',
                'unrestricted',
                'quantity'
            ])
            ->allowedFilters(InertiaHelper::filterBy([
                'book_stocks.batch',
                'book_stocks.plnt',
                'book_stocks.sloc',
                'book_stocks.qualinsp',
                'book_stocks.unrestricted',
                'book_stocks.quantity',
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
            'book_stocks.batch' => 'Batch',
            'book_stocks.plnt' => 'Plnt',
            'book_stocks.sloc' => 'SLoc',
            'book_stocks.qualinsp' => 'QualInsp',
            'book_stocks.unrestricted' => 'Unrestricted',
            'book_stocks.quantity' => 'Kuantitas',
            'materials.code' => 'Kode Material',
            'materials.description' => 'Deskripsi Material',
            'materials.uom' => 'UoM',
            'materials.mtyp' => 'MType'
        ])->addFilter(
            'materials.uom',
            'UoM',
            Material::select('uom')->groupBy('uom')->get()->pluck('uom', 'uom')->toArray()
        )->addFilter(
            'materials.mtyp',
            'MType',
            Material::select('mtyp')->groupBy('mtyp')->get()->pluck('mtyp', 'mtyp')->toArray()
        )->addColumns([
            'material_code' => 'Kode Material',
            'material_description' => 'Deskripsi Material',
            'uom' => 'UoM',
            'mtyp' => 'MType',
            'batch' => 'Batch',
            'plnt' => 'Plnt',
            'sloc' => 'SLoc',
            'qualinsp' => 'QualInsp',
            'unrestricted' => 'Unrestricted',
            'quantity' => 'Kuantitas',
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
            'material_code' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at')],
            'batch' => ['required', 'string', 'max:255'],
            'plnt' => ['required', 'integer', 'min:0'],
            'sloc' => ['required', 'integer', 'min:0'],
            'qualinsp' => ['required', 'integer', 'min:0'],
            'unrestricted' => ['required', 'numeric'],
            'quantity' => ['required', 'integer', 'min:0']
        ]);
        BookStock::create([
            'area_id' => (int)$request->area,
            'period_id' => (int)$request->period,
            'material_id' => Material::whereId($request->material_code)->first()?->id ?? 0,
            'batch' => Str::upper($request->batch),
            'plnt' => (int)$request->plnt,
            'sloc' => (int)$request->sloc,
            'qualinsp' => (int)$request->qualinsp,
            'unrestricted' => (float)$request->unrestricted,
            'quantity' => (int)$request->quantity
        ]);
    }

    /**
     * @param Request $request
     * @param BookStock $book
     * @throws Throwable
     * @throws ValidationException
     */
    public function update(Request $request, BookStock $book): void
    {
        $this->validate($request, [
            'area' => ['required', 'integer', Rule::exists('areas', 'id')->whereNull('deleted_at')],
            'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
            'material_code' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at')],
            'batch' => ['required', 'string', 'max:255'],
            'plnt' => ['required', 'integer', 'min:0'],
            'sloc' => ['required', 'integer', 'min:0'],
            'qualinsp' => ['required', 'integer', 'min:0'],
            'unrestricted' => ['required', 'numeric'],
            'quantity' => ['required', 'integer', 'min:0']
        ]);
        $book->updateOrFail([
            'area_id' => (int)$request->area,
            'period_id' => (int)$request->period,
            'material_id' => Material::whereId($request->material_code)->first()?->id ?? 0,
            'batch' => Str::upper($request->batch),
            'plnt' => (int)$request->plnt,
            'sloc' => (int)$request->sloc,
            'qualinsp' => (int)$request->qualinsp,
            'unrestricted' => (float)$request->unrestricted,
            'quantity' => (int)$request->quantity
        ]);
        $book->save();
    }

    /**
     * @param BookStock $book
     * @throws Throwable
     */
    public function destroy(BookStock $book): void
    {
        $book->deleteOrFail();
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
        Excel::import(new BookStocksImport(
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
        return MediaHelper::exportSpreadsheet(new BookStocksExport(
            Area::find(empty($request->query('area')) ? null : (int)$request->query('area')),
            Period::find(empty($request->query('period')) ? null : (int)$request->query('period'))
        ), new BookStock);
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return 'https://docs.google.com/spreadsheets/d/1v3iWj5ZJ-MUwNkVZI6VEDC_Cf3RufSnRREuYFewXyYg/edit?usp=sharing';
    }
}
