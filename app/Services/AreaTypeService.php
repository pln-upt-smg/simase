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
                DB::raw(
                    'date_format(area_types.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->whereNull('area_types.deleted_at')
            ->defaultSort('area_types.name')
            ->allowedFilters(InertiaHelper::filterBy(['area_types.name']))
            ->allowedSorts(['name', 'update_date'])
            ->paginate()
            ->withQueryString();
    }

    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table
            ->addSearchRows([
                'area_types.name' => 'Nama Tipe Area',
            ])
            ->addColumns([
                'name' => 'Nama Tipe Area',
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
            [
                'name' => 'Nama Tipe Area',
            ]
        );
        AreaType::create([
            'name' => Str::title($request->name),
        ]);
        $user = auth()->user();
        if (!is_null($user)) {
            $user->notify(
                new DataStored('Tipe Area', Str::title($request->name))
            );
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
            [
                'name' => 'Nama Tipe Area',
            ]
        );
        $areaType->updateOrFail([
            'name' => Str::title($request->name),
        ]);
        $areaType->save();
        $user = auth()->user();
        if (!is_null($user)) {
            $user->notify(
                new DataUpdated('Tipe Area', Str::title($request->name))
            );
        }
    }

    /**
     * @param AreaType $areaType
     * @throws Throwable
     */
    public function destroy(AreaType $areaType): void
    {
        $data = $areaType->name;
        $areaType->deleteOrFail();
        $user = auth()->user();
        if (!is_null($user)) {
            $user->notify(new DataDestroyed('Tipe Area', Str::title($data)));
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
        return 'https://docs.google.com/spreadsheets/d/1_iyLqpZbz09w22YRenD7kFuyidQJIUSf4-33jkZ8_kA/edit?usp=sharing';
    }

    /**
     * @param Request $request
     * @return AreaType|null
     */
    public function resolve(Request $request): ?AreaType
    {
        if (
            $request->query('area_types') === '0' ||
            $request->query('area_types') === 0
        ) {
            return null;
        }
        return AreaType::where(
            'id',
            $request->query('area_types')
                ? (int) $request->query('area_types')
                : 0
        )
            ->whereNull('deleted_at')
            ->first();
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
        $query = AreaType::orderBy('name')->whereNull('deleted_at');
        if (!is_null($request)) {
            $query = $query
                ->whereRaw(
                    'lower(name) like "%?%"',
                    Str::lower(trim($request->query('q') ?? ''))
                )
                ->limit(10);
        }
        return $query->get();
    }
}
