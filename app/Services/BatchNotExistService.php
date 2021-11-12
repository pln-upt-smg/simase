<?php

namespace App\Services;

use App\Exports\BatchNotExistsExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\MediaHelper;
use App\Models\ActualStock;
use App\Models\Area;
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

class BatchNotExistService
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
     * The default Batch Status value
     */
    private const DEFAULT_BATCH_NOT_EXISTS_STATUS = 'Batch tidak sesuai';

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
        $query = QueryBuilder::for(ActualStock::class)
            ->select([
                'actual_stocks.id as id',
                'actual_stocks.batch as batch_code',
                'actual_stocks.quantity as quantity',
                'materials.period_id as period_id',
                'materials.code as material_code',
                'materials.description as material_description',
                'materials.uom as uom',
                'materials.mtyp as mtyp',
                'sub_areas.name as sub_area_name',
                'users.name as creator_name',
                DB::raw("'" . self::DEFAULT_BATCH_NOT_EXISTS_STATUS . "' as batch_status"),
                DB::raw('date_format(actual_stocks.created_at, "%d/%m/%Y %H:%i") as creation_date')
            ])
            ->leftJoin('batches', 'batches.code', '=', 'actual_stocks.batch')
            ->leftJoin('sub_areas', 'sub_areas.id', '=', 'actual_stocks.sub_area_id')
            ->leftJoin('materials', 'materials.id', '=', 'actual_stocks.material_id')
            ->leftJoin('areas', 'areas.id', '=', 'sub_areas.area_id')
            ->leftJoin('users', 'users.id', '=', 'actual_stocks.user_id')
            ->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
            ->where('batches.area_id', '=', 'areas.id')
            ->whereNull(['batches.code', 'sub_areas.deleted_at', 'areas.deleted_at', 'actual_stocks.deleted_at', 'materials.deleted_at', 'users.deleted_at', 'periods.deleted_at']);
        if (!is_null($area)) {
            $query = $query->where('areas.id', $area->id);
        }
        if (!is_null($period)) {
            $query = $query->where('periods.id', $period->id);
        }
        return $query->defaultSort('materials.code')
            ->allowedFilters(InertiaHelper::filterBy([
                'actual_stocks.batch',
                'actual_stocks.quantity',
                'materials.code',
                'materials.description',
                'materials.uom',
                'materials.mtyp',
                'sub_areas.name',
                'users.name'
            ]))
            ->allowedSorts([
                'batch_code',
                'quantity',
                'material_code',
                'material_description',
                'uom',
                'mtyp',
                'sub_area_name',
                'creator_name',
                'batch_status',
                'creation_date'
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
            'sub_areas.name' => 'Sub Area',
            'materials.code' => 'Kode Material',
            'materials.description' => 'Deskripsi Material',
            'materials.uom' => 'UoM',
            'materials.mtyp' => 'MType',
            'actual_stocks.batch' => 'Kode Batch',
            'actual_stocks.quantity' => 'Kuantitas',
            'users.name' => 'Pembuat'
        ])->addColumns([
            'sub_area_name' => 'Sub Area',
            'material_code' => 'Kode Material',
            'material_description' => 'Deskripsi Material',
            'uom' => 'UoM',
            'mtyp' => 'MType',
            'batch_code' => 'Kode Batch',
            'quantity' => 'Kuantitas',
            'creation_date' => 'Tanggal Dibuat',
            'creator_name' => 'Pembuat',
            'batch_status' => 'Status'
        ]);
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(Request $request): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(new BatchNotExistsExport(
            $this,
            $this->areaService->resolve($request),
            $this->periodService->resolve($request)
        ), 'batch_not_exists');
    }

    /**
     * @param Area|null $area
     * @param Period|null $period
     * @return Collection
     */
    public function collection(?Area $area, ?Period $period): Collection
    {
        $query = ActualStock::select([
            DB::raw("'" . self::DEFAULT_BATCH_NOT_EXISTS_STATUS . "' as batch_status"),
            DB::raw('date_format(actual_stocks.created_at, "%d/%m/%Y %H:%i") as creation_date')
        ])
            ->leftJoin('batches', 'batches.code', '=', 'actual_stocks.batch')
            ->leftJoin('sub_areas', 'sub_areas.id', '=', 'actual_stocks.sub_area_id')
            ->leftJoin('materials', 'materials.id', '=', 'actual_stocks.material_id')
            ->leftJoin('users', 'users.id', '=', 'actual_stocks.user_id')
            ->leftJoin('areas', 'areas.id', '=', 'sub_areas.area_id')
            ->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
            ->orderBy('materials.code')
            ->where('batches.area_id', '=', 'areas.id')
            ->whereNull(['batches.code', 'sub_areas.deleted_at', 'areas.deleted_at', 'actual_stocks.deleted_at', 'materials.deleted_at', 'users.deleted_at', 'periods.deleted_at']);
        if (!is_null($area)) {
            $query = $query->where('areas.id', $area->id);
        }
        if (!is_null($period)) {
            $query = $query->where('periods.id', $period->id);
        }
        return $query->get()->load('subArea', 'material', 'user');
    }
}
