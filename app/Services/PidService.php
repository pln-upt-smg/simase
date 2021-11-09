<?php

namespace App\Services;

use App\Exports\PidExport;
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

class PidService
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
                'book_stocks.unrestricted as unrestricted',
                'book_stocks.qualinsp as qualinsp',
                'book_stocks.quantity as book_qty',
                'materials.period_id as period_id',
                'materials.code as material_code',
                'materials.description as material_description',
                'materials.uom as uom',
                'materials.mtyp as mtyp',
                DB::raw('case when actual_stocks.quantity is null then 0 else coalesce(actual_stocks.quantity, 0) end as actual_qty'),
                DB::raw('coalesce(((case when actual_stocks.quantity is null then 0 else coalesce(actual_stocks.quantity, 0) end) - book_stocks.quantity), 0) as gap_qty')
            ])
            ->leftJoin('actual_stocks', 'actual_stocks.material_id', '=', 'book_stocks.material_id')
            ->leftJoin('areas', 'areas.id', '=', 'book_stocks.area_id')
            ->leftJoin('materials', 'materials.id', '=', 'book_stocks.material_id')
            ->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
            ->whereNull(['book_stocks.deleted_at', 'actual_stocks.deleted_at', 'areas.deleted_at', 'materials.deleted_at', 'periods.deleted_at']);
        if (!is_null($area)) {
            $query = $query->where('areas.id', $area->id);
        }
        if (!is_null($period)) {
            $query = $query->where('periods.id', $period->id);
        }
        return $query->defaultSort('materials.code')
            ->allowedFilters(InertiaHelper::filterBy([
                'book_stocks.batch',
                'book_stocks.unrestricted',
                'book_stocks.qualinsp',
                'book_stocks.quantity',
                'materials.code',
                'materials.description',
                'materials.uom',
                'materials.mtyp'
            ]))
            ->allowedSorts([
                'batch_code',
                'unrestricted',
                'qualinsp',
                'book_qty',
                'material_code',
                'material_description',
                'uom',
                'mtyp',
                'actual_qty',
                'gap_qty'
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
            'book_stocks.unrestricted' => 'Unrestricted',
            'book_stocks.qualinsp' => 'QualInsp',
            'book_stocks.quantity' => 'BookQty'
        ])->addColumns([
            'material_code' => 'Kode Material',
            'material_description' => 'Deskripsi Material',
            'uom' => 'UoM',
            'mtyp' => 'MType',
            'batch_code' => 'Kode Batch',
            'unrestricted' => 'Unrestricted',
            'qualinsp' => 'QualInsp',
            'book_qty' => 'BookQty',
            'actual_qty' => 'ActualQty',
            'gap_qty' => 'GapQty'
        ]);
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(Request $request): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(new PidExport(
            $this->areaService->resolve($request),
            $this->periodService->resolve($request),
            $this
        ), 'pids');
    }

    /**
     * @param Area|null $area
     * @param Period|null $period
     * @return Collection
     */
    public function collection(?Area $area, ?Period $period): Collection
    {
        $query = BookStock::select([
            'book_stocks.id as id',
            'book_stocks.batch as batch_code',
            'book_stocks.unrestricted as unrestricted',
            'book_stocks.qualinsp as qualinsp',
            'book_stocks.quantity as book_qty',
            'materials.code as material_code',
            'materials.description as material_description',
            'materials.uom as uom',
            'materials.mtyp as mtyp',
            'areas.name as area_name',
            DB::raw('case when actual_stocks.quantity is null then 0 else coalesce(actual_stocks.quantity, 0) end as actual_qty'),
            DB::raw('coalesce((actual_stocks.quantity - book_stocks.quantity), 0) as gap_qty')
        ])
            ->orderBy('materials.code')
            ->leftJoin('actual_stocks', 'actual_stocks.material_id', '=', 'book_stocks.material_id')
            ->leftJoin('areas', 'areas.id', '=', 'book_stocks.area_id')
            ->leftJoin('materials', 'materials.id', '=', 'book_stocks.material_id')
            ->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
            ->whereNull(['book_stocks.deleted_at', 'actual_stocks.deleted_at', 'areas.deleted_at', 'materials.deleted_at', 'periods.deleted_at']);
        if (!is_null($area)) {
            $query = $query->where('areas.id', $area->id);
        }
        if (!is_null($period)) {
            $query = $query->where('periods.id', $period->id);
        }
        return $query->get();
    }
}
