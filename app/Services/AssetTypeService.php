<?php

namespace App\Services;

use App\Http\Helper\{InertiaHelper, MediaHelper};
use App\Imports\AssetTypeImport;
use App\Exports\AssetTypeExport;
use App\Models\AssetType;
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

class AssetTypeService
{
    use HasValidator;

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        return QueryBuilder::for(AssetType::class)
            ->select([
                'asset_types.id as id',
                'asset_types.name as name',
                'users.name as user_name',
                DB::raw(
                    'date_format(asset_types.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin('users', 'users.id', '=', 'asset_types.created_by')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->defaultSort('asset_types.name')
            ->allowedFilters(
                InertiaHelper::filterBy(['asset_types.name', 'users.name'])
            )
            ->allowedSorts(['name', 'user_name', 'update_date'])
            ->paginate()
            ->withQueryString();
    }

    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table
            ->addSearchRows([
                'asset_types.name' => 'Nama Tipe Aset',
                'users.name' => 'Pembuat',
            ])
            ->addColumns([
                'name' => 'Nama Tipe Aset',
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
                    Rule::unique('asset_types', 'name')->whereNull(
                        'deleted_at'
                    ),
                ],
            ],
            [],
            [
                'name' => 'Nama Tipe Aset',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            AssetType::create([
                'created_by' => $user->id,
                'name' => $request->name,
            ]);
            $user->notify(new DataStored('Tipe Aset', $request->name));
        }
    }

    /**
     * @param Request $request
     * @param AssetType $assetType
     * @throws Throwable
     */
    public function update(Request $request, AssetType $assetType): void
    {
        $this->validate(
            $request,
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('asset_types', 'name')
                        ->ignore($assetType->id)
                        ->whereNull('deleted_at'),
                ],
            ],
            [],
            [
                'name' => 'Nama Tipe Aset',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            $assetType->updateOrFail([
                'name' => $request->name,
            ]);
            $assetType->save();
            $user->notify(new DataUpdated('Tipe Aset', $request->name));
        }
    }

    /**
     * @param AssetType $assetType
     * @throws Throwable
     */
    public function destroy(AssetType $assetType): void
    {
        $data = $assetType->name;
        $user = auth()->user();
        if (!is_null($user)) {
            $assetType->deleteOrFail();
            $user->notify(new DataDestroyed('Tipe Aset', $data));
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
            new AssetTypeImport(auth()->user())
        );
    }

    /**
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(
            new AssetTypeExport($this),
            new AssetType()
        );
    }

    /**
     * @return string
     */
    public function template(): string
    {
        switch (auth()->user()->division_id ?? 0) {
            case 1:
                return 'https://docs.google.com/spreadsheets/d/1glsE3pKiyEwEHKBpRkbMT0ZY26MoRlxtGIqKnSvxkQk/edit?usp=sharing';
            default:
                return 'https://docs.google.com/spreadsheets/d/1JT4lowEPvsRl1eqL6QUij9uP8wAhPr20ejgQDgou7gA/edit?usp=sharing';
        }
    }

    /**
     * @param Request $request
     * @return AssetType|null
     */
    public function resolve(Request $request): ?AssetType
    {
        if (
            $request->query('asset_type') === '0' ||
            $request->query('asset_type') === 0
        ) {
            return null;
        }
        return AssetType::where(
            'id',
            $request->query('asset_type')
                ? (int) $request->query('asset_type')
                : 0
        )->first();
    }

    /**
     * @param Request $request
     * @return AssetType|null
     */
    public function single(Request $request): ?AssetType
    {
        return $this->collection($request)->first();
    }

    /**
     * @param Request|null $request
     * @return Collection
     */
    public function collection(?Request $request = null): Collection
    {
        $query = AssetType::orderBy('asset_types.name')
            ->select([
                'asset_types.id as id',
                'asset_types.name as name',
                'users.name as user_name',
                DB::raw(
                    'date_format(asset_types.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin('users', 'users.id', '=', 'asset_types.created_by')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0);
        if (!is_null($request)) {
            $query = $query->whereRaw(
                'lower(asset_types.name) like "%?%"',
                Str::lower(trim($request->query('q') ?? ''))
            );
        }
        return $query->get();
    }
}
