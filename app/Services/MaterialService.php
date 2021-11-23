<?php

namespace App\Services;

use App\Exports\MaterialsExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\JobHelper;
use App\Http\Helper\MediaHelper;
use App\Imports\MaterialsImport;
use App\Models\Material;
use App\Models\Period;
use App\Notifications\DataDestroyed;
use App\Notifications\DataStored;
use App\Notifications\DataUpdated;
use App\Services\Helper\HasValidator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class MaterialService
{
    use HasValidator;

    /**
     * @var PeriodService
     */
    private PeriodService $periodService;

    /**
     * @param PeriodService $periodService
     */
    public function __construct(PeriodService $periodService)
    {
        $this->periodService = $periodService;
    }

    /**
     * @param Period|null $period
     * @return LengthAwarePaginator
     */
    public function tableData(?Period $period): LengthAwarePaginator
    {
        $query = QueryBuilder::for(Material::class)
            ->select([
                'materials.id as id',
                'materials.period_id as period_id',
                'materials.code as code',
                'materials.description as description',
                'materials.uom as uom',
                'materials.mtyp as mtyp',
                'materials.crcy as crcy',
                'materials.price as price',
                'materials.per as per',
                DB::raw('date_format(materials.updated_at, "%d %b %Y") as update_date')
            ])
            ->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
            ->whereNull(['materials.deleted_at', 'periods.deleted_at']);
        if (!is_null($period)) {
            $query = $query->where('periods.id', $period->id);
        }
        return $query->defaultSort('materials.code')
            ->allowedFilters(InertiaHelper::filterBy([
                'materials.code',
                'materials.description',
                'materials.uom',
                'materials.mtyp',
                'materials.crcy',
                'materials.price',
                'materials.per'
            ]))
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
        ])->addColumns([
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
            'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:255', Rule::unique('materials', 'code')->where('period_id', $request->period)->whereNull('deleted_at')],
            'description' => ['required', 'string', 'max:255'],
            'uom' => ['required', 'string', 'max:255'],
            'mtyp' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'per' => ['required', 'integer', 'min:0']
        ], attributes: [
            'period' => 'Periode',
            'code' => 'Kode Material',
            'description' => 'Deskripsi Material',
            'uom' => 'UoM',
            'mtyp' => 'MType',
            'price' => 'Harga',
            'per' => 'Per'
        ]);
        Material::create([
            'period_id' => (int)$request->period,
            'code' => Str::upper($request->code),
            'description' => Str::title($request->description),
            'uom' => Str::upper($request->uom),
            'mtyp' => Str::upper($request->mtyp),
            'crcy' => Str::upper($request->crcy),
            'price' => (int)$request->price,
            'per' => (int)$request->per
        ]);
        auth()->user()?->notify(new DataStored('Material', Str::upper($request->code)));
    }

    /**
     * @param Request $request
     * @param Material $material
     * @throws Throwable
     */
    public function update(Request $request, Material $material): void
    {
        $this->validate($request, [
            'period' => ['required', 'integer', Rule::exists('periods', 'id')->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:255', Rule::unique('materials', 'code')->where('period_id', $request->period)->ignore($material->id)->whereNull('deleted_at')],
            'description' => ['required', 'string', 'max:255'],
            'uom' => ['required', 'string', 'max:255'],
            'mtyp' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'per' => ['required', 'integer', 'min:0']
        ], attributes: [
            'period' => 'Periode',
            'code' => 'Kode Material',
            'description' => 'Deskripsi Material',
            'uom' => 'UoM',
            'mtyp' => 'MType',
            'price' => 'Harga',
            'per' => 'Per'
        ]);
        $material->updateOrFail([
            'period_id' => (int)$request->period,
            'code' => Str::upper($request->code),
            'description' => Str::title($request->description),
            'uom' => Str::upper($request->uom),
            'mtyp' => Str::upper($request->mtyp),
            'crcy' => Str::upper($request->crcy),
            'price' => (int)$request->price,
            'per' => (int)$request->per
        ]);
        $material->save();
        auth()->user()?->notify(new DataUpdated('Material', Str::upper($request->code)));
    }

    /**
     * @param Material $material
     * @throws Throwable
     */
    public function destroy(Material $material): void
    {
        $data = $material->code;
        $material->deleteOrFail();
        auth()->user()?->notify(new DataDestroyed('Material', $data));
    }

    /**
     * @param Request $request
     * @throws ValidationException
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
        Excel::import(new MaterialsImport(
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
        return MediaHelper::exportSpreadsheet(new MaterialsExport(
            $this,
            $this->periodService->resolve($request)
        ), 'material_masters');
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return 'https://docs.google.com/spreadsheets/d/195BdCWN1KWWKSoImWSHFg-9vq67s-O7IsaMV7pAI-Mg/edit?usp=sharing';
    }

    /**
     * @param Request $request
     * @return Material|null
     */
    public function resolve(Request $request): ?Material
    {
        if ($request->query('material') === '0' || $request->query('material') === 0) {
            return null;
        }
        return Material::where('id', $request->query('material') ? (int)$request->query('material') : 0)->whereNull('deleted_at')->first()?->load('period');
    }

    /**
     * @param Request $request
     * @return Material|null
     */
    public function single(Request $request): ?Material
    {
        return $this->collection($request)->first();
    }

    /**
     * @param Request|null $request
     * @param Period|null $period
     * @return Collection
     */
    public function collection(?Request $request = null, ?Period $period = null): Collection
    {
        $query = Material::select([
            'materials.id as id',
            'materials.code as code',
            'materials.description as description',
            'materials.uom as uom',
            'materials.mtyp as mtyp',
            'materials.crcy as crcy',
            'materials.price as price',
            'materials.per as per',
            DB::raw('date_format(materials.updated_at, "%d-%b-%Y") as update_date')
        ])
            ->leftJoin('periods', 'periods.id', '=', 'materials.period_id')
            ->orderBy('materials.code')
            ->whereNull(['materials.deleted_at', 'periods.deleted_at']);
        if (!is_null($request)) {
            $query = $query->where('periods.id', $this->periodService->resolve($request)?->id ?? 0)
                ->whereRaw('lower(materials.code) like ?', '%' . Str::lower($request->query('q') ?? '') . '%');
        } else if (!is_null($period)) {
            $query = $query->where('periods.id', $period->id);
        }
        return $query->get();
    }
}
