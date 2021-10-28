<?php

namespace App\Services;

use App\Exports\MaterialsExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\MediaHelper;
use App\Imports\OperatorsImport;
use App\Models\Area;
use App\Models\Material;
use App\Models\Period;
use App\Services\Helper\HasValidator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class MaterialService
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
        $query = QueryBuilder::for(Material::class)
            ->select([
                'materials.id as id',
                'materials.code as code',
                'materials.description as description',
                'materials.uom as uom',
                'materials.mtyp as mtyp',
                'materials.crcy as crcy',
                'materials.price as price',
                'materials.per as per',
                DB::raw('date_format(materials.updated_at, "%d %b %Y") as update_date')
            ])
            ->whereNull('materials.deleted_at');

        if (!is_null($area)) {
            $query = $query->where('materials.area_id', $area->id);
        }

        if (!is_null($period)) {
            $query = $query->where('materials.period_id', $period->id);
        }

        return $query->defaultSort('code')
            ->allowedSorts([
                'code',
                'description',
                'uom',
                'mtyp',
                'crcy',
                'price',
                'per',
                'update_date'
            ])
            ->allowedFilters(InertiaHelper::filterBy([
                'materials.code',
                'materials.description',
                'materials.uom',
                'materials.mtyp',
                'materials.crcy',
                'materials.price',
                'materials.per'
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
            'materials.uom' => 'UoM',
            'materials.mtyp' => 'MType',
            'materials.crcy' => 'Currency',
            'materials.price' => 'Harga',
            'materials.per' => 'Per'
        ])->addFilter(
            'materials.uom',
            'UoM',
            Material::select('uom')->groupBy('uom')->get()->pluck('uom', 'uom')->toArray()
        )->addFilter(
            'materials.mtyp',
            'MType',
            Material::select('mtyp')->groupBy('mtyp')->get()->pluck('mtyp', 'mtyp')->toArray()
        )->addFilter(
            'materials.crcy',
            'Currency',
            Material::select('crcy')->groupBy('crcy')->get()->pluck('crcy', 'crcy')->toArray()
        )->addColumns([
            'code' => 'Kode Material',
            'description' => 'Deskripsi Material',
            'uom' => 'UoM',
            'mtyp' => 'MType',
            'crcy' => 'Currency',
            'price' => 'Harga',
            'per' => 'Per',
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
            'area' => ['required', 'int', 'exists:areas,id'],
            'period' => ['required', 'int', 'exists:periods,id'],
            'code' => ['required', 'string', 'max:255', 'unique:materials'],
            'description' => ['required', 'string', 'max:255'],
            'uom' => ['required', 'string', 'max:255'],
            'mtyp' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'per' => ['required', 'integer', 'min:0']
        ]);
        Material::create([
            'area_id' => $request->area,
            'period_id' => $request->period,
            'code' => Str::upper($request->code),
            'description' => Str::title($request->description),
            'uom' => Str::upper($request->uom),
            'mtyp' => Str::upper($request->mtyp),
            'crcy' => Str::upper($request->crcy),
            'price' => $request->price,
            'per' => $request->per
        ]);
    }

    /**
     * @param Request $request
     * @param Material $material
     * @throws Throwable
     * @throws ValidationException
     */
    public function update(Request $request, Material $material): void
    {
        $this->validate($request, [
            'area' => ['required', 'int', 'exists:areas,id'],
            'period' => ['required', 'int', 'exists:periods,id'],
            'code' => ['required', 'string', 'max:255', Rule::unique('materials')->ignore($material->id)->whereNull('deleted_at')],
            'description' => ['required', 'string', 'max:255'],
            'uom' => ['required', 'string', 'max:255'],
            'mtyp' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'per' => ['required', 'integer', 'min:0']
        ]);
        $material->updateOrFail([
            'area_id' => $request->area,
            'period_id' => $request->period,
            'code' => Str::upper($request->code),
            'description' => Str::title($request->description),
            'uom' => Str::upper($request->uom),
            'mtyp' => Str::upper($request->mtyp),
            'crcy' => Str::upper($request->crcy),
            'price' => $request->price,
            'per' => $request->per
        ]);
        $material->save();
    }

    /**
     * @param Material $material
     * @throws Throwable
     */
    public function destroy(Material $material): void
    {
        $material->deleteOrFail();
    }

    /**
     * @param Request $request
     * @throws Throwable
     */
    public function import(Request $request): void
    {
        MediaHelper::importSpreadsheet($request, new OperatorsImport);
    }

    /**
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(new MaterialsExport(), new Material);
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return 'https://docs.google.com/spreadsheets/d/1uOA5ear--StRXSFf_iIYVW-50daP4KmA1vOcDxIRZoo/edit?usp=sharing';
    }
}
