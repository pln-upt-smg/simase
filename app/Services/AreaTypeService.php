<?php

namespace App\Services;

use App\Http\Helper\{InertiaHelper, MediaHelper};
use App\Imports\AreaTypeImport;
use App\Exports\AreaTypeExport;
use App\Models\AreaType;
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

class AreaTypeService
{
    use HasValidator;

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        return QueryBuilder::for(AreaType::class)
            ->select([
                'area_types.id as id',
                'area_types.name as name',
                'users.name as user_name',
                DB::raw(
                    'date_format(area_types.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin('users', 'users.id', '=', 'area_types.created_by')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->defaultSort('area_types.name')
            ->allowedFilters(
                InertiaHelper::filterBy(['area_types.name', 'users.name'])
            )
            ->allowedSorts(['name', 'user_name', 'update_date'])
            ->paginate()
            ->withQueryString();
    }

    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table
            ->addSearchRows([
                'area_types.name' => 'Nama Tipe Area',
                'users.name' => 'Pembuat',
            ])
            ->addColumns([
                'name' => 'Nama Tipe Area',
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
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('area_types', 'name')->whereNull('deleted_at'),
                ],
            ],
            [],
            [
                'name' => 'Nama Tipe Area',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            AreaType::create([
                'created_by' => $user->id,
                'name' => $request->name,
            ]);
            $user->notify(new DataStored('Tipe Area', $request->name));
        }
    }

    /**
     * @param Request $request
     * @param AreaType $areaType
     * @throws Throwable
     */
    public function update(Request $request, AreaType $areaType): void
    {
        $this->validate(
            $request,
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('area_types', 'name')
                        ->ignore($areaType->id)
                        ->whereNull('deleted_at'),
                ],
            ],
            [],
            [
                'name' => 'Nama Tipe Area',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            $areaType->updateOrFail([
                'name' => $request->name,
            ]);
            $areaType->save();
            $user->notify(new DataUpdated('Tipe Area', $request->name));
        }
    }

    /**
     * @param AreaType $areaType
     * @throws Throwable
     */
    public function destroy(AreaType $areaType): void
    {
        $data = $areaType->name;
        $user = auth()->user();
        if (!is_null($user)) {
            $areaType->deleteOrFail();
            $user->notify(new DataDestroyed('Tipe Area', $data));
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
            new AreaTypeImport(auth()->user())
        );
    }

    /**
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(
            new AreaTypeExport($this),
            new AreaType()
        );
    }

    /**
     * @return string
     */
    public function template(): string
    {
        switch (auth()->user()->division_id ?? 0) {
            case 1:
                return 'https://docs.google.com/spreadsheets/d/1d6rdMAstOEt_PMw2TVL2Yw4n7OLJ9RziiXZypXQlFig/edit?usp=sharing';
            default:
                return 'https://docs.google.com/spreadsheets/d/1upvekPxwdnzcWBRgIed1BgU3IEm-VMopNzJhqZsugSc/edit?usp=sharing';
        }
    }

    /**
     * @param Request $request
     * @return AreaType|null
     */
    public function resolve(Request $request): ?AreaType
    {
        if (
            $request->query('area_type') === '0' ||
            $request->query('area_type') === 0
        ) {
            return null;
        }
        return AreaType::where(
            'id',
            $request->query('area_type')
                ? (int) $request->query('area_type')
                : 0
        )->first();
    }

    /**
     * @param Request $request
     * @return AreaType|null
     */
    public function single(Request $request): ?AreaType
    {
        return $this->collection($request)->first();
    }

    /**
     * @param Request|null $request
     * @return Collection
     */
    public function collection(?Request $request = null): Collection
    {
        $query = AreaType::orderBy('area_types.name')
            ->select([
                'area_types.id as id',
                'area_types.name as name',
                'users.name as user_name',
                DB::raw(
                    'date_format(area_types.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin('users', 'users.id', '=', 'area_types.created_by')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0);
        if (!is_null($request)) {
            $query = $query->whereRaw(
                'lower(area_types.name) like "%?%"',
                Str::lower(trim($request->query('q') ?? ''))
            );
        }
        return $query->get();
    }
}
