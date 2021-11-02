<?php

namespace App\Services;

use App\Exports\PidDetailExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\MediaHelper;
use App\Models\ActualStock;
use App\Models\BookStock;
use App\Models\Period;
use App\Services\Helper\HasValidator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class PidDetailService
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
     * @param Period|null $period
     * @return LengthAwarePaginator
     */
    public function tableData(?Period $period): LengthAwarePaginator
    {
        $query = QueryBuilder::for(BookStock::class)
            ->select([
                'book_stocks.id as id',
                'book_stocks.batch as batch_code',
                'materials.code as material_code',
                'materials.description as material_description',
                DB::raw('coalesce((select sum(actual_stocks.quantity) from actual_stocks where actual_stocks.material_id = book_stocks.material_id), 0) as sum_quantity')
            ])
            ->leftJoin('materials', 'materials.id', '=', 'book_stocks.material_id')
            ->whereNull(['book_stocks.deleted_at', 'materials.deleted_at']);
        if (!is_null($period)) {
            $query = $query->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
                ->where('periods.id', $period->id)
                ->whereNull('periods.deleted_at');
        }
        return $query->defaultSort('materials.code')
            ->allowedSorts([
                'book_stocks.batch',
                'materials.code',
                'materials.description',
                'sum_quantity'
            ])
            ->allowedFilters(InertiaHelper::filterBy([
                'book_stocks.batch',
                'materials.code',
                'materials.description'
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
            'book_stocks.batch' => 'Kode Batch'
        ])->addColumns([
            'material_code' => 'Kode Material',
            'material_description' => 'Deskripsi Material',
            'batch_code' => 'Kode Batch',
            'sum_quantity' => 'Jumlah Kuantitas'
        ]);
    }

    /**
     * @param Period|null $period
     * @return array
     */
    public function tableAreaData(?Period $period): array
    {
        // make sure to intialize an empty result array
        $result = [];

        // get the area name list
        $areas = $this->areaService->collection();

        // get the book stock id list
        $stocks = BookStock::leftJoin('materials', 'materials.id', '=', 'book_stocks.material_id')
            ->whereNull(['book_stocks.deleted_at', 'materials.deleted_at']);

        // apply the period condition on the stock list
        if (!is_null($period)) {
            $stocks = $stocks->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
                ->where('periods.id', $period->id)
                ->whereNull('periods.deleted_at');
        }

        // fetch the stock!
        $stocks = $stocks->get();

        // loop over the stock list
        foreach ($stocks as $stock) {

            // make sure to initialize area data
            $areaData = [];

            // loop over the area name list
            foreach ($areas as $area) {

                // each iteration, fetch the quantity of the stock based on the material and area
                $query = ActualStock::leftJoin('materials', 'materials.id', '=', 'actual_stocks.material_id')
                    ->leftJoin('areas', 'areas.id', '=', 'materials.area_id')
                    ->where('materials.id', $stock->material_id)
                    ->where('areas.id', $area->id)
                    ->whereNull(['actual_stocks.deleted_at', 'materials.deleted_at', 'areas.deleted_at']);

                // dont forget to apply the period query!
                if (!is_null($period)) {
                    $query = $query->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
                        ->where('periods.id', $period->id)
                        ->whereNull('periods.deleted_at');
                }

                // fetch it and append the quantity to areaData (default is 0)
                $areaData[] = $query->first()?->quantity ?? 0;
            }

            // append the data to result array
            $result[$stock->id] = $areaData;
        }

        // return the result
        return $result;
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(Request $request): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(new PidDetailExport(
            $this->periodService->resolve($request),
            $this->areaService,
            $this
        ), 'pid_details');
    }

    public function collection(?Period $period): Collection
    {
        $query = BookStock::select([
            'book_stocks.id as id',
            'book_stocks.batch as batch_code',
            'materials.code as material_code',
            'materials.description as material_description',
            DB::raw('(select sum(actual_stocks.quantity) from actual_stocks where actual_stocks.material_id = book_stocks.material_id) as sum_quantity')
        ])
            ->orderBy('materials.code')
            ->leftJoin('materials', 'materials.id', '=', 'book_stocks.material_id')
            ->whereNull(['book_stocks.deleted_at', 'materials.deleted_at']);
        if (!is_null($period)) {
            $query = $query->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
                ->where('periods.id', $period->id)
                ->whereNull('periods.deleted_at');
        }
        return $query->get();
    }
}
