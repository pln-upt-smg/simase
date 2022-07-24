<?php

namespace App\Services;

use App\Http\Helper\{InertiaHelper, MediaHelper};
use App\Imports\CertificateImport;
use App\Exports\CertificateExport;
use App\Models\Certificate;
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

class CertificateService
{
    use HasValidator;

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        return QueryBuilder::for(Certificate::class)
            ->select([
                'users.name as user_name',
                DB::raw(
                    'date_format(certificates.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin('users', 'users.id', '=', 'certificates.created_by')
            ->allowedFilters(
                InertiaHelper::filterBy(['certificates.name', 'users.name'])
            )
            ->allowedSorts(['name', 'user_name', 'update_date'])
            ->paginate()
            ->withQueryString();
    }

    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table
            ->addSearchRows([
                'certificates.name' => 'Nama Sertifikat',
                'users.name' => 'Pembuat',
            ])
            ->addColumns([
                'name' => 'Nama Sertifikat',
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
                    Rule::unique('certificates', 'name')->whereNull(
                        'deleted_at'
                    ),
                ],
            ],
            [
                'name' => 'Nama Sertifikat',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            Certificate::create([
                'created_by' => $user->id,
                'name' => $request->name,
            ]);
            $user->notify(new DataStored('Sertifikat', $request->name));
        }
    }

    /**
     * @param Request $request
     * @param Certificate $certificate
     * @throws Throwable
     */
    public function update(Request $request, Certificate $certificate): void
    {
        $this->validate(
            $request,
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('certificates', 'name')
                        ->ignore($certificate->id)
                        ->whereNull('deleted_at'),
                ],
            ],
            [
                'name' => 'Nama Sertifikat',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            $certificate->updateOrFail([
                'name' => $request->name,
            ]);
            $certificate->save();
            $user->notify(new DataUpdated('Sertifikat', $request->name));
        }
    }

    /**
     * @param Certificate $certificate
     * @throws Throwable
     */
    public function destroy(Certificate $certificate): void
    {
        $data = $certificate->name;
        $user = auth()->user();
        if (!is_null($user)) {
            $certificate->deleteOrFail();
            $user->notify(new DataDestroyed('Sertifikat', $data));
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
            new CertificateImport(auth()->user())
        );
    }

    /**
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(
            new CertificateExport($this),
            new Certificate()
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
     * @return Certificate|null
     */
    public function resolve(Request $request): ?Certificate
    {
        if (
            $request->query('certificate') === '0' ||
            $request->query('certificate') === 0
        ) {
            return null;
        }
        return Certificate::where(
            'id',
            $request->query('certificate')
                ? (int) $request->query('certificate')
                : 0
        )->first();
    }

    /**
     * @param Request $request
     * @return Certificate|null
     */
    public function single(Request $request): ?Certificate
    {
        return $this->collection($request)->first();
    }

    /**
     * @param Request|null $request
     * @return Collection
     */
    public function collection(?Request $request = null): Collection
    {
        $query = Certificate::orderBy('name')->limit(10);
        if (!is_null($request)) {
            $query = $query->whereRaw(
                'lower(name) like "%?%"',
                Str::lower(trim($request->query('q') ?? ''))
            );
        }
        return $query->get();
    }
}