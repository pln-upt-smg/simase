<?php

namespace App\Services;

use App\Http\Helper\{InertiaHelper, MediaHelper};
use App\Exports\AssetSubmissionExport;
use App\Models\AssetSubmission;
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

class AssetSubmissionService
{
    use HasValidator;

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        return QueryBuilder::for(AssetSubmission::class)
            ->select([
                'assets.id as asset_id',
                'assets.techidentno as techidentno',
                'assets.name as name',
                'assets.quantity as quantity',
                'asset_types.id as asset_type_id',
                'asset_types.name as asset_type_name',
                'areas.id as area_id',
                'areas.funcloc as area_funcloc',
                'areas.name as area_name',
                'area_types.name as area_type_name',
                'users.name as user_name',
                'asset_submissions.id as id',
                'asset_submissions.note as asset_submission_note',
                'asset_submissions.quantity as asset_submission_quantity',
                'asset_submissions.priority as asset_submission_priority',
                DB::raw(
                    'date_format(asset_submissions.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin('users', 'users.id', '=', 'asset_submissions.created_by')
            ->leftJoin('assets', 'assets.id', '=', 'asset_submissions.asset_id')
            ->leftJoin(
                'asset_types',
                'asset_types.id',
                '=',
                'assets.asset_type_id'
            )
            ->leftJoin('areas', 'areas.id', '=', 'assets.area_id')
            ->leftJoin('area_types', 'area_types.id', '=', 'areas.area_type_id')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->defaultSort('assets.name')
            ->allowedFilters(
                InertiaHelper::filterBy([
                    'assets.techidentno',
                    'assets.name',
                    'assets.quantity',
                    'asset_types.name',
                    'areas.funcloc',
                    'areas.name',
                    'area_types.name',
                    'users.name',
                    'asset_submissions.note',
                    'asset_submissions.quantity',
                    'asset_submissions.priority',
                ])
            )
            ->allowedSorts([
                'techidentno',
                'name',
                'quantity',
                'asset_type_name',
                'asset_type_uom',
                'area_funcloc',
                'area_name',
                'area_type_name',
                'user_name',
                'asset_submission_note',
                'asset_submission_quantity',
                'asset_submission_priority',
                'update_date',
            ])
            ->paginate()
            ->withQueryString();
    }

    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table
            ->addSearchRows([
                'assets.techidentno' => 'Techidentno',
                'assets.name' => 'Nama Aset',
                'assets.quantity' => 'Kuantitas',
                'asset_types.name' => 'Tipe Aset',
                'areas.funcloc' => 'Funcloc',
                'areas.name' => 'Area',
                'area_types.name' => 'Tipe Area',
                'users.name' => 'Pelapor',
                'asset_submissions.note' => 'Keterangan',
                'asset_submissions.quantity' => 'Pengajuan Kuantitas',
                'asset_submissions.priority' => 'Prioritas',
            ])
            ->addColumns([
                'techidentno' => 'Techidentno',
                'name' => 'Nama Aset',
                'quantity' => 'Kuantitas',
                'asset_type_name' => 'Tipe Aset',
                'asset_type_uom' => 'UoM',
                'area_funcloc' => 'Funcloc',
                'area_name' => 'Area',
                'area_type_name' => 'Tipe Area',
                'user_name' => 'Pelapor',
                'asset_submission_note' => 'Keterangan',
                'asset_submission_quantity' => 'Pengajuan Kuantitas',
                'asset_submission_priority' => 'Prioritas',
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
                'asset.id' => [
                    'required',
                    'integer',
                    Rule::exists('assets', 'id')->whereNull('deleted_at'),
                ],
                'note' => ['nullable', 'string'],
                'quantity' => ['required', 'numeric', 'min:1'],
                'priority' => ['required', 'numeric', 'min:1', 'max:3'],
            ],
            [],
            [
                'asset.id' => 'Aset',
                'note' => 'Keterangan',
                'quantity' => 'Pengajuan Kuantitas',
                'priority' => 'Prioritas',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            AssetSubmission::create([
                'asset_id' => (int) $request->asset['id'],
                'note' => $request->note,
                'quantity' => (int) $request->quantity,
                'priority' => (int) $request->priority,
                'created_by' => $user->id,
            ]);
            $user->notify(
                new DataStored('Laporan Pengajuan Aset', $request->name)
            );
        }
    }

    /**
     * @param Request $request
     * @param AssetSubmission $assetSubmission
     * @throws Throwable
     */
    public function update(
        Request $request,
        AssetSubmission $assetSubmission
    ): void {
        $this->validate(
            $request,
            [
                'asset.id' => [
                    'required',
                    'integer',
                    Rule::exists('assets', 'id')->whereNull('deleted_at'),
                ],
                'note' => ['nullable', 'string'],
                'quantity' => ['required', 'numeric', 'min:1'],
                'priority' => ['required', 'numeric', 'min:1', 'max:3'],
            ],
            [],
            [
                'asset.id' => 'Aset',
                'note' => 'Keterangan',
                'quantity' => 'Pengajuan Kuantitas',
                'priority' => 'Prioritas',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            $assetSubmission->updateOrFail([
                'asset_id' => (int) $request->asset['id'],
                'note' => $request->note,
                'quantity' => (int) $request->quantity,
                'priority' => (int) $request->priority,
            ]);
            $assetSubmission->save();
            $user->notify(
                new DataUpdated('Laporan Pengajuan Aset', $request->name)
            );
        }
    }

    /**
     * @param AssetSubmission $assetSubmission
     * @throws Throwable
     */
    public function destroy(AssetSubmission $assetSubmission): void
    {
        $data = $assetSubmission->name;
        $user = auth()->user();
        if (!is_null($user)) {
            $assetSubmission->deleteOrFail();
            $user->notify(new DataDestroyed('Laporan Pengajuan Aset', $data));
        }
    }

    /**
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(
            new AssetSubmissionExport($this),
            new AssetSubmission()
        );
    }

    /**
     * @param Request $request
     * @return AssetSubmission|null
     */
    public function resolve(Request $request): ?AssetSubmission
    {
        if (
            $request->query('asset_submission') === '0' ||
            $request->query('asset_submission') === 0
        ) {
            return null;
        }
        return AssetSubmission::where(
            'id',
            $request->query('asset_submission')
                ? (int) $request->query('asset_submission')
                : 0
        )->first();
    }

    /**
     * @param Request $request
     * @return AssetSubmission|null
     */
    public function single(Request $request): ?AssetSubmission
    {
        return $this->collection($request)->first();
    }

    /**
     * @param Request|null $request
     * @param bool $latest
     * @param int $limit
     * @return Collection
     */
    public function collection(
        ?Request $request = null,
        bool $latest = false,
        int $limit = 10
    ): Collection {
        $query = AssetSubmission::orderBy(
            $latest ? 'assets.created_at' : 'assets.name'
        )
            ->select([
                'assets.id as asset_id',
                'assets.techidentno as techidentno',
                'assets.name as name',
                'assets.quantity as quantity',
                'asset_types.id as asset_type_id',
                'asset_types.name as asset_type_name',
                'areas.id as area_id',
                'areas.funcloc as area_funcloc',
                'areas.name as area_name',
                'area_types.name as area_type_name',
                'users.name as user_name',
                'asset_submissions.id as id',
                'asset_submissions.note as asset_submission_note',
                'asset_submissions.quantity as asset_submission_quantity',
                'asset_submissions.priority as asset_submission_priority',
                DB::raw(
                    'date_format(asset_submissions.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin('users', 'users.id', '=', 'asset_submissions.created_by')
            ->leftJoin('assets', 'assets.id', '=', 'asset_submissions.asset_id')
            ->leftJoin(
                'asset_types',
                'asset_types.id',
                '=',
                'assets.asset_type_id'
            )
            ->leftJoin('areas', 'areas.id', '=', 'assets.area_id')
            ->leftJoin('area_types', 'area_types.id', '=', 'areas.area_type_id')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->limit($limit);
        if (!is_null($request)) {
            $query = $query
                ->leftJoin(
                    'assets',
                    'assets.id',
                    '=',
                    'asset_submissions.asset_id'
                )
                ->whereRaw(
                    'lower(assets.name) like "%?%"',
                    Str::lower(trim($request->query('q') ?? ''))
                )
                ->orWhereRaw(
                    'lower(asset_submissions.quantity) like "%?%"',
                    Str::lower(trim($request->query('q') ?? ''))
                );
        }
        return $query->get();
    }
}
