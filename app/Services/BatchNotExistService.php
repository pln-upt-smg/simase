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

    private const DEFAULT_BATCH_NOT_EXISTS_STATUS = 'Batch tidak sesuai';

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
                'materials.code as material_code',
                'materials.description as material_description',
                'materials.uom as uom',
                'materials.mtyp as mtyp',
                'areas.name as area_name',
                'users.name as creator_name',
                DB::raw("'" . self::DEFAULT_BATCH_NOT_EXISTS_STATUS . "' as batch_status"),
                DB::raw('date_format(actual_stocks.created_at, "%d/%m/%Y %H:%i") as creation_date')
            ])
            ->leftJoin('materials', 'materials.id', '=', 'actual_stocks.material_id')
            ->leftJoin('areas', 'areas.id', '=', 'materials.area_id')
            ->leftJoin('users', 'users.id', '=', 'actual_stocks.user_id')
            ->leftJoin('batches', 'batches.code', '=', 'actual_stocks.batch')
            ->whereNull(['batches.code', 'actual_stocks.deleted_at', 'materials.deleted_at', 'users.deleted_at']);
        if (!is_null($area)) {
            $query = $query->where('materials.area_id', $area->id);
        }
        if (!is_null($period)) {
            $query = $query->where('materials.period_id', $period->id);
        }
        return $query->defaultSort('material_code')
            ->allowedSorts([
                'batch_code',
                'quantity',
                'material_code',
                'material_description',
                'uom',
                'mtyp',
                'area_name',
                'creator_name',
                'creation_date',
                'batch_status'
            ])
            ->allowedFilters(InertiaHelper::filterBy([
                'batch_code',
                'quantity',
                'material_code',
                'material_description',
                'uom',
                'mtyp',
                'area_name',
                'creator_name',
                'creation_date',
                'batch_status'
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
            'area_name' => 'Area',
            'material_code' => 'Kode Material',
            'material_description' => 'Deskripsi Material',
            'uom' => 'UoM',
            'mtyp' => 'MType',
            'batch_code' => 'Kode Batch',
            'quantity' => 'Kuantitas',
            'creation_date' => 'Tanggal Dibuat',
            'creator_name' => 'Pembuat',
            'batch_status' => 'Status'
        ])->addColumns([
            'area_name' => 'Area',
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
            Area::find(empty($request->query('area')) ? null : (int)$request->query('area')),
            Period::find(empty($request->query('period')) ? null : (int)$request->query('period')),
            $this
        ), 'batch_not_exists');
    }

    /**
     * @param Area|null $area
     * @param Period|null $period
     * @return Collection
     */
    public function collection(?Area $area, ?Period $period): Collection
    {
        $query = ActualStock::orderBy('materials.code')
            ->select([
                'actual_stocks.id as id',
                'actual_stocks.batch as batch_code',
                'actual_stocks.quantity as quantity',
                'materials.code as material_code',
                'materials.description as material_description',
                'materials.uom as uom',
                'materials.mtyp as mtyp',
                'areas.name as area_name',
                'users.nip as creator_nip',
                DB::raw("'" . self::DEFAULT_BATCH_NOT_EXISTS_STATUS . "' as batch_status"),
                DB::raw('date_format(actual_stocks.created_at, "%d/%m/%Y %H:%i") as creation_date')
            ])
            ->leftJoin('materials', 'materials.id', '=', 'book_stocks.material_id')
            ->leftJoin('areas', 'areas.id', '=', 'materials.area_id')
            ->leftJoin('users', 'users.id', '=', 'actual_stocks.user_id')
            ->leftJoin('batches', 'batches.code', '=', 'actual_stocks.batch')
            ->whereNull(['batches.code', 'actual_stocks.deleted_at', 'materials.deleted_at', 'users.deleted_at']);
        if (!is_null($area)) {
            $query = $query->where('materials.area_id', $area->id);
        }
        if (!is_null($period)) {
            $query = $query->where('materials.period_id', $period->id);
        }
        return $query->get();
    }
}
