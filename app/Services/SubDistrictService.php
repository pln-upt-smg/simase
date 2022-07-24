<?php

namespace App\Services;

use App\Http\Helper\{InertiaHelper, MediaHelper};
use App\Imports\SubDistrictImport;
use App\Exports\SubDistrictExport;
use App\Models\SubDistrict;
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

class SubDistrictService
{
    use HasValidator;

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        return QueryBuilder::for(SubDistrict::class)
            ->select([
                'users.name as user_name',
                DB::raw(
                    'date_format(sub_districts.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin('users', 'users.id', '=', 'sub_districts.created_by')
            ->allowedFilters(
                InertiaHelper::filterBy(['sub_districts.name', 'users.name'])
            )
            ->allowedSorts(['name', 'user_name', 'update_date'])
            ->paginate()
            ->withQueryString();
    }

    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table
            ->addSearchRows([
                'sub_districts.name' => 'Nama Kecamatan',
                'users.name' => 'Pembuat',
            ])
            ->addColumns([
                'name' => 'Nama Kecamatan',
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
                    Rule::unique('sub_districts', 'name')->whereNull(
                        'deleted_at'
                    ),
                ],
            ],
            [
                'name' => 'Nama Kecamatan',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            SubDistrict::create([
                'created_by' => $user->id,
                'name' => $request->name,
            ]);
            $user->notify(new DataStored('Kecamatan', $request->name));
        }
    }

    /**
     * @param Request $request
     * @param SubDistrict $subDistrict
     * @throws Throwable
     */
    public function update(Request $request, SubDistrict $subDistrict): void
    {
        $this->validate(
            $request,
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('sub_districts', 'name')
                        ->ignore($subDistrict->id)
                        ->whereNull('deleted_at'),
                ],
            ],
            [
                'name' => 'Nama Kecamatan',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            $subDistrict->updateOrFail([
                'name' => $request->name,
            ]);
            $subDistrict->save();
            $user->notify(new DataUpdated('Kecamatan', $request->name));
        }
    }

    /**
     * @param SubDistrict $subDistrict
     * @throws Throwable
     */
    public function destroy(SubDistrict $subDistrict): void
    {
        $data = $subDistrict->name;
        $user = auth()->user();
        if (!is_null($user)) {
            $subDistrict->deleteOrFail();
            $user->notify(new DataDestroyed('Kecamatan', $data));
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
            new SubDistrictImport(auth()->user())
        );
    }

    /**
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(
            new SubDistrictExport($this),
            new SubDistrict()
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
     * @return SubDistrict|null
     */
    public function resolve(Request $request): ?SubDistrict
    {
        if (
            $request->query('sub_district') === '0' ||
            $request->query('sub_district') === 0
        ) {
            return null;
        }
        return SubDistrict::where(
            'id',
            $request->query('sub_district')
                ? (int) $request->query('sub_district')
                : 0
        )->first();
    }

    /**
     * @param Request $request
     * @return SubDistrict|null
     */
    public function single(Request $request): ?SubDistrict
    {
        return $this->collection($request)->first();
    }

    /**
     * @param Request|null $request
     * @return Collection
     */
    public function collection(?Request $request = null): Collection
    {
        $query = SubDistrict::orderBy('name')->limit(10);
        if (!is_null($request)) {
            $query = $query->whereRaw(
                'lower(name) like "%?%"',
                Str::lower(trim($request->query('q') ?? ''))
            );
        }
        return $query->get();
    }
}
