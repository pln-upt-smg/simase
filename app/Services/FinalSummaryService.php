<?php

namespace App\Services;

use App\Exports\FinalSummaryExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\MediaHelper;
use App\Models\Area;
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

class FinalSummaryService
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
                'book_stocks.area_id as area_id',
                'book_stocks.unrestricted as unrestricted',
                'book_stocks.qualinsp as qualinsp',
                'book_stocks.material_id as material_id',
                'materials.period_id as period_id',
                'materials.code as material_code',
                'materials.description as material_description',
                'materials.uom as uom',
                'materials.mtyp as mtyp',
                'areas.sloc as sloc',
	            DB::raw('coalesce((select sum(actual_stocks.quantity) from actual_stocks inner join areas on areas.id = sub_areas.area_id where actual_stocks.material_id = materials.id and actual_stocks.sub_area_id = sub_areas.id and actual_stocks.deleted_at is null), 0) as total_stock'),
	            DB::raw('(coalesce((select sum(actual_stocks.quantity) from actual_stocks inner join areas on areas.id = sub_areas.area_id where actual_stocks.material_id = materials.id and actual_stocks.sub_area_id = sub_areas.id and actual_stocks.deleted_at is null), 0) - coalesce((select sum(book_stocks.quantity) from book_stocks where book_stocks.material_id = materials.id and book_stocks.area_id = areas.id and book_stocks.deleted_at is null), 0)) as gap_stock'),
	            DB::raw('((coalesce((select sum(actual_stocks.quantity) from actual_stocks inner join areas on areas.id = sub_areas.area_id where actual_stocks.material_id = materials.id and actual_stocks.sub_area_id = sub_areas.id and actual_stocks.deleted_at is null), 0) - coalesce((select sum(book_stocks.quantity) from book_stocks where book_stocks.material_id = materials.id and book_stocks.area_id = areas.id and book_stocks.deleted_at is null), 0)) * materials.price) as gap_value')
            ])
            ->leftJoin('areas', 'areas.id', '=', 'book_stocks.area_id')
            ->leftJoin('sub_areas', 'sub_areas.area_id', '=', 'areas.id')
            ->leftJoin('materials', 'materials.id', '=', 'book_stocks.material_id')
            ->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
            ->whereNull(['book_stocks.deleted_at', 'materials.deleted_at', 'periods.deleted_at']);
        if (!is_null($period)) {
            $query = $query->where('periods.id', $period->id);
        }
        return $query->defaultSort('materials.code')
            ->allowedFilters(InertiaHelper::filterBy([
                'book_stocks.unrestricted',
                'book_stocks.qualinsp',
                'materials.code',
                'materials.description',
                'materials.uom',
                'materials.mtyp'
            ]))
            ->allowedSorts([
                'unrestricted',
                'qualinsp',
                'material_code',
                'material_description',
                'uom',
                'mtyp',
                'total_stock',
                'gap_stock',
                'gap_value'
            ])
            ->paginate()
            ->withQueryString();
    }

    /**
     * @param Area|null $area
     * @param Period|null $period
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function gapValueRankTableData(?Area $area, ?Period $period, int $limit = 10): LengthAwarePaginator
    {
        $query = QueryBuilder::for(BookStock::class)
            ->select([
                'book_stocks.id as id',
                'book_stocks.unrestricted as unrestricted',
                'book_stocks.qualinsp as qualinsp',
                'book_stocks.material_id as material_id',
                'materials.code as material_code',
                'materials.description as material_description',
                'materials.uom as uom',
                'materials.mtyp as mtyp',
                'areas.sloc as sloc',
	            DB::raw('coalesce((select sum(actual_stocks.quantity) from actual_stocks inner join areas on areas.id = sub_areas.area_id where actual_stocks.material_id = materials.id and actual_stocks.sub_area_id = sub_areas.id and actual_stocks.deleted_at is null), 0) as total_stock'),
	            DB::raw('(coalesce((select sum(actual_stocks.quantity) from actual_stocks inner join areas on areas.id = sub_areas.area_id where actual_stocks.material_id = materials.id and actual_stocks.sub_area_id = sub_areas.id and actual_stocks.deleted_at is null), 0) - coalesce((select sum(book_stocks.quantity) from book_stocks where book_stocks.material_id = materials.id and book_stocks.area_id = areas.id and book_stocks.deleted_at is null), 0)) as gap_stock'),
	            DB::raw('((coalesce((select sum(actual_stocks.quantity) from actual_stocks inner join areas on areas.id = sub_areas.area_id where actual_stocks.material_id = materials.id and actual_stocks.sub_area_id = sub_areas.id and actual_stocks.deleted_at is null), 0) - coalesce((select sum(book_stocks.quantity) from book_stocks where book_stocks.material_id = materials.id and book_stocks.area_id = areas.id and book_stocks.deleted_at is null), 0)) * materials.price) as gap_value')
            ])
            ->leftJoin('areas', 'areas.id', '=', 'book_stocks.area_id')
            ->leftJoin('sub_areas', 'sub_areas.area_id', '=', 'areas.id')
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
            ->orderBy('gap_value')
            ->limit($limit)
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
            'areas.sloc' => 'SLoc',
            'book_stocks.unrestricted' => 'Unrestricted',
            'book_stocks.qualinsp' => 'QualInsp'
        ])->addColumns([
            'material_code' => 'Kode Material',
            'material_description' => 'Deskripsi Material',
            'uom' => 'UoM',
            'mtyp' => 'MType',
            'sloc' => 'SLoc',
            'unrestricted' => 'Unrestricted',
            'qualinsp' => 'QualInsp',
            'total_stock' => 'Total Stock',
            'gap_stock' => 'Gap Stock',
            'gap_value' => 'Gap Value'
        ]);
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(Request $request): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(new FinalSummaryExport(
            $this,
            $this->periodService->resolve($request)
        ), 'final_summaries');
    }

    /**
     * @param Period|null $period
     * @return Collection
     */
    public function collection(?Period $period): Collection
    {
        $query = BookStock::orderBy('materials.code')
            ->select([
                'book_stocks.id as id',
                'book_stocks.unrestricted as unrestricted',
                'book_stocks.qualinsp as qualinsp',
                'book_stocks.material_id as material_id',
                'materials.code as material_code',
                'materials.description as material_description',
                'materials.uom as uom',
                'materials.mtyp as mtyp',
                'areas.sloc as sloc',
                DB::raw('coalesce((select sum(actual_stocks.quantity) from actual_stocks inner join areas on areas.id = sub_areas.area_id where actual_stocks.material_id = materials.id and actual_stocks.sub_area_id = sub_areas.id and actual_stocks.deleted_at is null), 0) as total_stock'),
                DB::raw('(coalesce((select sum(actual_stocks.quantity) from actual_stocks inner join areas on areas.id = sub_areas.area_id where actual_stocks.material_id = materials.id and actual_stocks.sub_area_id = sub_areas.id and actual_stocks.deleted_at is null), 0) - coalesce((select sum(book_stocks.quantity) from book_stocks where book_stocks.material_id = materials.id and book_stocks.area_id = areas.id and book_stocks.deleted_at is null), 0)) as gap_stock'),
                DB::raw('((coalesce((select sum(actual_stocks.quantity) from actual_stocks inner join areas on areas.id = sub_areas.area_id where actual_stocks.material_id = materials.id and actual_stocks.sub_area_id = sub_areas.id and actual_stocks.deleted_at is null), 0) - coalesce((select sum(book_stocks.quantity) from book_stocks where book_stocks.material_id = materials.id and book_stocks.area_id = areas.id and book_stocks.deleted_at is null), 0)) * materials.price) as gap_value')
            ])
            ->leftJoin('areas', 'areas.id', '=', 'book_stocks.area_id')
            ->leftJoin('sub_areas', 'sub_areas.area_id', '=', 'areas.id')
            ->leftJoin('materials', 'materials.id', '=', 'book_stocks.material_id')
            ->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
            ->whereNull(['book_stocks.deleted_at', 'areas.deleted_at', 'materials.deleted_at', 'periods.deleted_at']);
        if (!is_null($period)) {
            $query = $query->where('periods.id', $period->id);
        }
        return $query->get();
    }

    /**
     * @param Period|null $period
     * @return array
     */
    public function chart(?Period $period): array
    {
        $result = [];
        $areas = $this->areaService->collection();
        foreach ($areas as $area) {
            $query = BookStock::select([
                DB::raw('sum(((coalesce((select sum(actual_stocks.quantity) from actual_stocks inner join areas on areas.id = sub_areas.area_id where actual_stocks.material_id = materials.id and actual_stocks.sub_area_id = sub_areas.id and actual_stocks.deleted_at is null), 0) - coalesce((select sum(book_stocks.quantity) from book_stocks where book_stocks.material_id = materials.id and book_stocks.area_id = areas.id and book_stocks.deleted_at is null), 0)) * materials.price)) as gap_value')
            ])
                ->leftJoin('areas', 'areas.id', '=', 'book_stocks.area_id')
                ->leftJoin('sub_areas', 'sub_areas.area_id', '=', 'areas.id')
                ->leftJoin('materials', 'materials.id', '=', 'book_stocks.material_id')
                ->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
                ->where('areas.id', $area->id)
                ->whereNull(['book_stocks.deleted_at', 'areas.deleted_at', 'materials.deleted_at', 'periods.deleted_at']);
            if (!is_null($period)) {
                $query = $query->where('periods.id', $period->id);
            }
            $result[] = $query->first()?->gap_value ?: 0;
        }
        return $result;
    }
}
