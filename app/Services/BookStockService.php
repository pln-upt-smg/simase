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
                'book_stocks.batch as batch_code',
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
            ->whereNull(['book_stocks.deleted_at', 'materials.deleted_at']);
        if (!is_null($area)) {
            $query = $query->leftJoin('areas', 'areas.id', '=', 'materials.area_id')
                ->where('areas.id', $area->id)
                ->whereNull('areas.deleted_at');
        }
        if (!is_null($period)) {
            $query = $query->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
                ->where('periods.id', $period->id)
                ->whereNull('periods.deleted_at');
        }
        return $query->defaultSort('materials.code')
            ->allowedFilters(InertiaHelper::filterBy([
                'book_stocks.batch',
                'book_stocks.quantity',
                'book_stocks.plnt',
                'book_stocks.sloc',
                'book_stocks.qualinsp',
                'book_stocks.unrestricted',
                'materials.code',
                'materials.description',
                'materials.uom',
                'materials.mtyp'
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
            'book_stocks.sloc' => 'SLoc',
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
            'material_code' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at')],
            'batch_code' => ['required', 'string', 'max:255'],
            'plnt' => ['required', 'integer', 'min:0'],
            'sloc' => ['required', 'integer', 'min:0'],
            'qualinsp' => ['required', 'integer', 'min:0'],
            'unrestricted' => ['required', 'numeric'],
            'quantity' => ['required', 'numeric', 'min:0']
        ], attributes: [
            'area' => 'Area',
            'period' => 'Periode',
            'material_code' => 'Kode Material',
            'batch_code' => 'Kode Batch',
            'plnt' => 'Plnt',
            'sloc' => 'SLoc',
            'qualinsp' => 'QualInsp',
            'unrestricted' => 'Unrestricted',
            'quantity' => 'Kuantitas'
        ]);
        BookStock::create([
            'material_id' => Material::whereRaw('lower(code) = lower(?)', $request->material_code)->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at')->first()?->id ?? 0,
            'batch' => Str::upper($request->batch_code),
            'plnt' => (int)$request->plnt,
            'sloc' => (int)$request->sloc,
            'qualinsp' => (int)$request->qualinsp,
            'unrestricted' => (float)$request->unrestricted,
            'quantity' => (int)$request->quantity
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
            'material_code' => ['required', 'string', 'max:255', Rule::exists('materials', 'code')->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at')],
            'batch_code' => ['required', 'string', 'max:255'],
            'plnt' => ['required', 'integer', 'min:0'],
            'sloc' => ['required', 'integer', 'min:0'],
            'qualinsp' => ['required', 'integer', 'min:0'],
            'unrestricted' => ['required', 'numeric'],
            'quantity' => ['required', 'numeric', 'min:0']
        ], attributes: [
            'area' => 'Area',
            'period' => 'Periode',
            'material_code' => 'Kode Material',
            'batch_code' => 'Kode Batch',
            'plnt' => 'Plnt',
            'sloc' => 'SLoc',
            'qualinsp' => 'QualInsp',
            'unrestricted' => 'Unrestricted',
            'quantity' => 'Kuantitas'
        ]);
        $book->updateOrFail([
            'material_id' => Material::whereRaw('lower(code) = lower(?)', $request->material_code)->where('area_id', $request->area)->where('period_id', $request->period)->whereNull('deleted_at')->first()?->id ?? 0,
            'batch' => Str::upper($request->batch_code),
            'plnt' => (int)$request->plnt,
            'sloc' => (int)$request->sloc,
            'qualinsp' => (int)$request->qualinsp,
            'unrestricted' => (float)$request->unrestricted,
            'quantity' => (int)$request->quantity
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
            'file' => ['required', 'mimes:xls,xlsx,csv', 'max:' . MediaHelper::SPREADSHEET_MAX_SIZE]
        ], attributes: [
            'period' => 'Periode',
            'file' => 'File'
        ]);
        $import = new BookStocksImport(Period::where('id', (int)$request->period)->first());
        Excel::import($import, $request->file('file'));
        auth()->user()?->notify(new DataImported('Book Stock', $import->getRowCount()));
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(Request $request): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(new BookStocksExport(
            $this->areaService->resolve($request),
            $this->periodService->resolve($request),
            $this
        ), new BookStock);
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
        $query = BookStock::leftJoin('materials', 'materials.id', '=', 'book_stocks.material_id')
            ->whereNull(['book_stocks.deleted_at', 'materials.deleted_at']);
        if (!is_null($area)) {
            $query = $query->leftJoin('areas', 'areas.id', '=', 'materials.area_id')
                ->where('areas.id', $area->id)
                ->whereNull('areas.deleted_at');
        }
        if (!is_null($period)) {
            $query = $query->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
                ->where('periods.id', $period->id)
                ->whereNull('periods.deleted_at');
        }
        return $query->orderBy('materials.code')->get()->load(['material', 'material.area']);
    }
}
