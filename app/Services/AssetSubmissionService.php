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
                'assets.id as id',
                'assets.name as name',
                'assets.quantity as quantity',
                'asset_types.name as asset_type_name',
                'asset_types.uom as asset_type_uom',
                'areas.name as area_name',
                'area_types.name as area_type_name',
                'users.name as user_name',
                'asset_submissions.note as asset_submission_note',
                'asset_submissions.quantity as asset_submission_quantity',
                'asset_submissions.priority as asset_submission_priority',
                DB::raw(
                    'date_format(asset_submissions.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin('assets', 'assets.id', '=', 'asset_submissions.asset_id')
            ->leftJoin(
                'asset_types',
                'asset_types.id',
                '=',
                'assets.asset_type_id'
            )
            ->leftJoin('areas', 'areas.id', '=', 'assets.area_id')
            ->leftJoin('area_types', 'area_types.id', '=', 'areas.area_type_id')
            ->whereNull('asset_submissions.deleted_at')
            ->defaultSort('assets.name')
            ->allowedFilters(
                InertiaHelper::filterBy([
                    'assets.name',
                    'assets.quantity',
                    'asset_types.name',
                    'asset_types.uom',
                    'areas.name',
                    'area_types.name',
                    'users.name',
                    'asset_submissions.note',
                    'asset_submissions.quantity',
                    'asset_submissions.priority',
                ])
            )
            ->allowedSorts([
                'name',
                'quantity',
                'asset_type_name',
                'asset_type_uom',
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
                'assets.name' => 'Nama Aset',
                'assets.quantity' => 'Kuantitas',
                'asset_types.name' => 'Tipe Aset',
                'asset_types.uom' => 'UoM',
                'areas.name' => 'Area',
                'area_types.name' => 'Tipe Area',
                'users.name' => 'Pelapor',
                'asset_submissions.note' => 'Keterangan',
                'asset_submissions.quantity' => 'Pengajuan Kuantitas',
                'asset_submissions.priority' => 'Prioritas',
            ])
            ->addColumns([
                'name' => 'Nama Aset',
                'quantity' => 'Kuantitas',
                'asset_type_name' => 'Tipe Aset',
                'asset_type_uom' => 'UoM',
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
                'asset' => [
                    'required',
                    'integer',
                    Rule::exists('assets', 'id')->whereNull('deleted_at'),
                ],
                'note' => ['nullable', 'string'],
                'quantity' => ['required', 'numeric', 'min:1'],
                'priority' => ['required', 'numeric', 'min:1', 'max:3'],
            ],
            [
                'asset' => 'Aset',
                'note' => 'Keterangan',
                'quantity' => 'Pengajuan Kuantitas',
                'priority' => 'Prioritas',
            ]
        );
        AssetSubmission::create([
            'asset_id' => (int) $request->asset,
            'note' => $request->name,
            'quantity' => (int) $request->quantity,
            'priority' => (int) $request->priority,
            'created_by' => auth()->user()->id,
        ]);
        if (auth()->user()) {
            auth()
                ->user()
                ->notify(
                    new DataStored(
                        'Laporan Pengajuan Aset',
                        Str::title($request->name)
                    )
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
                'asset' => [
                    'required',
                    'integer',
                    Rule::exists('assets', 'id')->whereNull('deleted_at'),
                ],
                'note' => ['nullable', 'string'],
                'quantity' => ['required', 'numeric', 'min:1'],
                'priority' => ['required', 'numeric', 'min:1', 'max:3'],
            ],
            [
                'asset' => 'Aset',
                'note' => 'Keterangan',
                'quantity' => 'Pengajuan Kuantitas',
                'priority' => 'Prioritas',
            ]
        );
        $assetSubmission->updateOrFail([
            'asset_id' => (int) $request->asset,
            'note' => $request->name,
            'quantity' => (int) $request->quantity,
            'priority' => (int) $request->priority,
        ]);
        $assetSubmission->save();
        if (auth()->user()) {
            auth()
                ->user()
                ->notify(
                    new DataUpdated(
                        'Laporan Pengajuan Aset',
                        Str::title($request->name)
                    )
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
        $assetSubmission->deleteOrFail();
        if (auth()->user()) {
            auth()
                ->user()
                ->notify(
                    new DataDestroyed(
                        'Laporan Pengajuan Aset',
                        Str::title($data)
                    )
                );
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
     * @return string
     */
    public function template(): string
    {
        return 'https://docs.google.com/spreadsheets/d/1_iyLqpZbz09w22YRenD7kFuyidQJIUSf4-33jkZ8_kA/edit?usp=sharing';
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
        )
            ->whereNull('deleted_at')
            ->first();
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
     * @return Collection
     */
    public function collection(?Request $request = null): Collection
    {
        $query = AssetSubmission::orderBy('name')->whereNull('deleted_at');
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
                )
                ->limit(10);
        }
        return $query->get();
    }
}
