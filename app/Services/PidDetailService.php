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
     * @param AreaService $areaService
     */
    public function __construct(AreaService $areaService)
    {
        $this->areaService = $areaService;
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
                'materials.area_id as area_id',
                'materials.period_id as period_id',
                'materials.code as material_code',
                'materials.description as material_description',
                DB::raw('coalesce((select sum(actual_stocks.quantity) from actual_stocks where actual_stocks.material_id = book_stocks.material_id), 0) as sum_quantity')
            ])
            ->leftJoin('materials', 'materials.id', '=', 'book_stocks.material_id')
            ->whereNull(['book_stocks.deleted_at', 'materials.deleted_at']);
        if (!is_null($period)) {
            $query = $query->where('materials.period_id', $period->id);
        }
        return $query->defaultSort('material_code')
            ->allowedSorts([
                'batch_code',
                'material_code',
                'material_description',
                'sum_quantity'
            ])
            ->allowedFilters(InertiaHelper::filterBy([
                'batch_code',
                'material_code',
                'material_description',
                'sum_quantity'
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
            'material_code' => 'Kode Material',
            'material_description' => 'Deskripsi Material',
            'batch_code' => 'Kode Batch',
            'sum_quantity' => 'Jumlah Kuantitas'
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
        $stocks = BookStock::select(['id', 'material_id', 'deleted_at'])->whereNull('book_stocks.deleted_at');

        // apply the period condition on the stock list
        if (!is_null($period)) {
            $stocks = $stocks->where('materials.period_id', $period->id);
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
                $query = ActualStock::select('quantity')
                    ->leftJoin('materials', 'materials.id', '=', 'actual_stocks.material_id')
                    ->where('actual_stocks.material_id', $stock->material_id)
                    ->where('materials.area_id', $area->id)
                    ->whereNull(['actual_stocks.deleted_at', 'materials.deleted_at']);

                // dont forget to apply the period query!
                if (!is_null($period)) {
                    $query = $query->where('period_id', $period->id);
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
            Period::find(empty($request->query('period')) ? null : (int)$request->query('period')),
            $this->areaService,
            $this
        ), 'pid_details');
    }

    public function collection(?Period $period): Collection
    {
        $query = BookStock::orderBy('materials.code')
            ->select([
                'book_stocks.id as id',
                'book_stocks.batch as batch_code',
                'materials.code as material_code',
                'materials.description as material_description',
                DB::raw('(select sum(actual_stocks.quantity) from actual_stocks where actual_stocks.material_id = book_stocks.material_id) as sum_quantity')
            ])
            ->leftJoin('materials', 'materials.id', '=', 'book_stocks.material_id')
            ->whereNull(['book_stocks.deleted_at', 'materials.deleted_at']);
        if (!is_null($period)) {
            $query = $query->where('period_id', $period->id);
        }
        return $query->get();
    }
}
