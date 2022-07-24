<?php

namespace App\Services;

use App\Http\Helper\{InertiaHelper, MediaHelper};
use App\Imports\DistrictImport;
use App\Exports\DistrictExport;
use App\Models\District;
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

class DistrictService
{
    use HasValidator;

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        return QueryBuilder::for(District::class)
            ->select([
                'users.name as user_name',
                DB::raw(
                    'date_format(districts.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin('users', 'users.id', '=', 'districts.created_by')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->allowedFilters(
                InertiaHelper::filterBy(['districts.name', 'users.name'])
            )
            ->allowedSorts(['name', 'user_name', 'update_date'])
            ->paginate()
            ->withQueryString();
    }

    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table
            ->addSearchRows([
                'districts.name' => 'Nama Kabupaten / Kotamadya',
                'users.name' => 'Pembuat',
            ])
            ->addColumns([
                'name' => 'Nama Kabupaten / Kotamadya',
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
                    Rule::unique('districts', 'name')->whereNull('deleted_at'),
                ],
            ],
            [
                'name' => 'Nama Kabupaten / Kotamadya',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            District::create([
                'created_by' => $user->id,
                'name' => $request->name,
            ]);
            $user->notify(
                new DataStored('Kabupaten / Kotamadya', $request->name)
            );
        }
    }

    /**
     * @param Request $request
     * @param District $district
     * @throws Throwable
     */
    public function update(Request $request, District $district): void
    {
        $this->validate(
            $request,
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('districts', 'name')
                        ->ignore($district->id)
                        ->whereNull('deleted_at'),
                ],
            ],
            [
                'name' => 'Nama Kabupaten / Kotamadya',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            $district->updateOrFail([
                'name' => $request->name,
            ]);
            $district->save();
            $user->notify(
                new DataUpdated('Kabupaten / Kotamadya', $request->name)
            );
        }
    }

    /**
     * @param District $district
     * @throws Throwable
     */
    public function destroy(District $district): void
    {
        $data = $district->name;
        $user = auth()->user();
        if (!is_null($user)) {
            $district->deleteOrFail();
            $user->notify(new DataDestroyed('Kabupaten / Kotamadya', $data));
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
            new DistrictImport(auth()->user())
        );
    }

    /**
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(
            new DistrictExport($this),
            new District()
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
     * @return District|null
     */
    public function resolve(Request $request): ?District
    {
        if (
            $request->query('district') === '0' ||
            $request->query('district') === 0
        ) {
            return null;
        }
        return District::where(
            'id',
            $request->query('district') ? (int) $request->query('district') : 0
        )->first();
    }

    /**
     * @param Request $request
     * @return District|null
     */
    public function single(Request $request): ?District
    {
        return $this->collection($request)->first();
    }

    /**
     * @param Request|null $request
     * @return Collection
     */
    public function collection(?Request $request = null): Collection
    {
        $query = District::orderBy('districts.name')
            ->leftJoin('users', 'users.id', '=', 'districts.created_by')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->limit(10);
        if (!is_null($request)) {
            $query = $query->whereRaw(
                'lower(districts.name) like "%?%"',
                Str::lower(trim($request->query('q') ?? ''))
            );
        }
        return $query->get();
    }
}
