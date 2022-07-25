<?php

namespace App\Services;

use App\Http\Helper\{InertiaHelper, MediaHelper};
use App\Imports\AreaImport;
use App\Exports\AreaExport;
use App\Models\Area;
use App\Notifications\{DataDestroyed, DataStored, DataUpdated};
use App\Services\Helpers\HasValidator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\{Collection, Str};
use Illuminate\Validation\{Rule, ValidationException};
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class AreaService
{
    use HasValidator;

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        return QueryBuilder::for(Area::class)
            ->select([
                'areas.id as id',
                'areas.funcloc as funcloc',
                'areas.name as name',
                'areas.lat as latitude',
                'areas.lon as longitude',
                'area_types.id as area_type_id',
                'area_types.name as area_type',
                'users.name as user_name',
                DB::raw(
                    'date_format(areas.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin('users', 'users.id', '=', 'areas.created_by')
            ->leftJoin('area_types', 'area_types.id', '=', 'areas.area_type_id')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->defaultSort('areas.funcloc')
            ->allowedFilters(
                InertiaHelper::filterBy([
                    'areas.funcloc',
                    'areas.name',
                    'area_types.name',
                    'users.name',
                ])
            )
            ->allowedSorts([
                'funcloc',
                'name',
                'area_type',
                'user_name',
                'update_date',
            ])
            ->paginate()
            ->withQueryString();
    }

    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table
            ->addSearchRows([
                'areas.funcloc' => 'Funcloc',
                'areas.name' => 'Nama Area',
                'areas.lat' => 'Latitude',
                'areas.lon' => 'Longitude',
                'area_types.name' => 'Tipe Area',
                'users.name' => 'Pembuat',
            ])
            ->addColumns([
                'funcloc' => 'Funcloc',
                'name' => 'Nama Area',
                'latitude' => 'Latitude',
                'longitude' => 'Longitude',
                'area_type' => 'Tipe Area',
                'user_name' => 'Pembuat',
                'update_date' => 'Tanggal Pembaruan',
                'action' => 'Aksi',
            ]);
    }

    /**
     * @param Request $request
     * @throws ValidationException
     */
    public function store(Request $request): void
    {
        $this->validate(
            $request,
            [
                'type' => [
                    'required',
                    'integer',
                    Rule::exists('area_types', 'id')->whereNull('deleted_at'),
                ],
                'funcloc' => [
                    'required',
                    'numeric',
                    Rule::unique('areas', 'funcloc')->whereNull('deleted_at'),
                ],
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('areas', 'name')->whereNull('deleted_at'),
                ],
                'latitude' => ['required', 'numeric'],
                'longitude' => ['required', 'numeric'],
            ],
            [],
            [
                'type' => 'Tipe Area',
                'funcloc' => 'Funcloc',
                'name' => 'Nama Area',
                'latitude' => 'Latitude',
                'longitude' => 'Longitude',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            Area::create([
                'created_by' => $user->id,
                'area_type_id' => (int) $request->type,
                'funcloc' => $request->funcloc,
                'name' => $request->name,
                'lat' => $request->latitude,
                'lon' => $request->longitude,
            ]);
            $user->notify(new DataStored('Area', $request->name));
        }
    }

    /**
     * @param Request $request
     * @param Area $area
     * @throws Throwable
     */
    public function update(Request $request, Area $area): void
    {
        $this->validate(
            $request,
            [
                'type' => [
                    'required',
                    'integer',
                    Rule::exists('area_types', 'id')->whereNull('deleted_at'),
                ],
                'funcloc' => [
                    'required',
                    'numeric',
                    Rule::unique('areas', 'funcloc')
                        ->ignore($area->id)
                        ->whereNull('deleted_at'),
                ],
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('areas', 'name')
                        ->ignore($area->id)
                        ->whereNull('deleted_at'),
                ],
                'latitude' => ['required', 'numeric'],
                'longitude' => ['required', 'numeric'],
            ],
            [],
            [
                'type' => 'Tipe Area',
                'funcloc' => 'Funcloc',
                'name' => 'Nama Area',
                'latitude' => 'Latitude',
                'longitude' => 'Longitude',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            $area->updateOrFail([
                'area_type_id' => (int) $request->type,
                'funcloc' => $request->funcloc,
                'name' => $request->name,
                'lat' => $request->latitude,
                'lon' => $request->longitude,
            ]);
            $area->save();
            $user->notify(new DataUpdated('Area', $request->name));
        }
    }

    /**
     * @param Area $area
     * @throws Throwable
     */
    public function destroy(Area $area): void
    {
        $data = $area->name;
        $user = auth()->user();
        if (!is_null($user)) {
            $area->deleteOrFail();
            $user->notify(new DataDestroyed('Area', $data));
        }
    }

    /**
     * @param Request $request
     * @throws Throwable
     */
    public function import(Request $request): void
    {
        MediaHelper::importSpreadsheet(
            $request,
            new AreaImport(auth()->user())
        );
    }

    /**
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(
            new AreaExport($this),
            new Area()
        );
    }

    /**
     * @return string
     */
    public function template(): string
    {
        switch (auth()->user()->division_id ?? 0) {
            case 1:
                return 'https://docs.google.com/spreadsheets/d/1u9sZ11XfmFCJ7IUcl_EU4gXAJFcZlI410l9JYZQI43Q/edit?usp=sharing';
            default:
                return 'https://docs.google.com/spreadsheets/d/1pRwXXtOLdTxEVIaXu75WBUIy1NlX-6RkRskW0ZOmt_w/edit?usp=sharing';
        }
    }

    /**
     * @param Request $request
     * @return Area|null
     */
    public function resolve(Request $request): ?Area
    {
        if ($request->query('area') === '0' || $request->query('area') === 0) {
            return null;
        }
        return Area::where(
            'id',
            $request->query('area') ? (int) $request->query('area') : 0
        )->first();
    }

    /**
     * @param Request $request
     * @return Area|null
     */
    public function single(Request $request): ?Area
    {
        return $this->collection($request)->first();
    }

    /**
     * @param Request|null $request
     * @return Collection
     */
    public function collection(?Request $request = null): Collection
    {
        $query = Area::orderBy('areas.name')
            ->select([
                'areas.id as id',
                'areas.funcloc as funcloc',
                'areas.name as name',
                'areas.lat as latitude',
                'areas.lon as longitude',
                'area_types.id as area_type_id',
                'area_types.name as area_type',
                'users.name as user_name',
                DB::raw(
                    'date_format(areas.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin('users', 'users.id', '=', 'areas.created_by')
            ->leftJoin('area_types', 'area_types.id', '=', 'areas.area_type_id')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->limit(10);
        if (!is_null($request)) {
            $filter = Str::lower(trim($request->query('q') ?? ''));
            $query = $query->where(function ($query) use ($filter) {
                $query
                    ->orWhereRaw("lower(areas.funcloc) like (?)", ["%{$filter}%"])
                    ->orWhereRaw("lower(areas.name) like (?)", ["%{$filter}%"]);
            });
        }
        return $query->get();
    }
}
