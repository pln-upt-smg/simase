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
                'certificates.name as name',
                'certificates.area_code as area_code',
                'certificates.certificate_type as certificate_type',
                'certificates.certificate_number as certificate_number',
                'certificates.certificate_print_number as certificate_print_number',
                'certificates.certificate_bookkeeping_date as certificate_bookkeeping_date',
                'certificates.certificate_publishing_date as certificate_publishing_date',
                'certificates.certificate_final_date as certificate_final_date',
                'certificates.nib as nib',
                'certificates.origin_right_category as origin_right_category',
                'certificates.base_registration_decree_number as base_registration_decree_number',
                'certificates.base_registration_date as base_registration_date',
                'certificates.measuring_letter_number as measuring_letter_number',
                'certificates.measuring_letter_date as measuring_letter_date',
                'certificates.measuring_letter_status as measuring_letter_status',
                'certificates.field_map_status as field_map_status',
                'certificates.wide as wide',
                'certificate_files.file as certificate_file',
                'urban_villages.id as urban_village_id',
                'urban_villages.name as urban_village_name',
                'sub_districts.id as sub_district_id',
                'sub_districts.name as sub_district_name',
                'districts.id as district_id',
                'districts.name as district_name',
                'provinces.id as province_id',
                'provinces.name as province_name',
                'holders.id as holder_id',
                'holders.name as holder_name',
                'users.name as user_name',
                DB::raw(
                    'date_format(certificates.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin(
                'certificate_files',
                'certificate_files.certificate_id',
                '=',
                'certificates.id'
            )
            ->leftJoin(
                'urban_villages',
                'urban_villages.id',
                '=',
                'certificates.urban_village_id'
            )
            ->leftJoin(
                'sub_districts',
                'sub_districts.id',
                '=',
                'certificates.sub_district_id'
            )
            ->leftJoin(
                'districts',
                'districts.id',
                '=',
                'certificates.district_id'
            )
            ->leftJoin(
                'provinces',
                'provinces.id',
                '=',
                'certificates.province_id'
            )
            ->leftJoin('holders', 'holders.id', '=', 'certificates.holder_id')
            ->leftJoin('users', 'users.id', '=', 'certificates.created_by')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->allowedFilters(
                InertiaHelper::filterBy([
                    'certificates.name',
                    'certificates.area_code',
                    'certificates.certificate_type',
                    'certificates.certificate_number',
                    'certificates.certificate_print_number',
                    'certificates.certificate_bookkeeping_date',
                    'certificates.certificate_publishing_date',
                    'certificates.certificate_final_date',
                    'certificates.nib',
                    'certificates.origin_right_category',
                    'certificates.base_registration_decree_number',
                    'certificates.base_registration_date',
                    'certificates.measuring_letter_number',
                    'certificates.measuring_letter_date',
                    'certificates.measuring_letter_status',
                    'certificates.field_map_status',
                    'certificates.wide',
                    'urban_villages.name',
                    'sub_districts.name',
                    'districts.name',
                    'provinces.name',
                    'holders.name',
                    'users.name',
                ])
            )
            ->allowedSorts([
                'name',
                'area_code',
                'certificate_type',
                'certificate_number',
                'certificate_print_number',
                'certificate_bookkeeping_date',
                'certificate_publishing_date',
                'certificate_final_date',
                'nib',
                'origin_right_category',
                'base_registration_decree_number',
                'base_registration_date',
                'measuring_letter_number',
                'measuring_letter_date',
                'measuring_letter_status',
                'field_map_status',
                'wide',
                'urban_village_name',
                'sub_district_name',
                'district_name',
                'province_name',
                'holder_name',
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
                'certificates.name' => 'Nama Sertifikat',
                'certificates.area_code' => 'Kode Wilayah',
                'certificates.certificate_type' => 'Tipe Sertifikat',
                'certificates.certificate_number' => 'Nomor Sertifikat',
                'certificates.certificate_print_number' =>
                    'Nomor Cetak Sertifikat',
                'certificates.certificate_bookkeeping_date' =>
                    'Tanggal Pembukuan',
                'certificates.certificate_publishing_date' =>
                    'Tanggal Penerbitan',
                'certificates.certificate_final_date' =>
                    'Tanggal Akhir / Perpanjangan',
                'certificates.nib' => 'NIB',
                'certificates.origin_right_category' => 'Kategori Asal Hak',
                'certificates.base_registration_decree_number' =>
                    'Surat Keputusan',
                'certificates.base_registration_date' =>
                    'Tanggal Dasar Pendaftaran',
                'certificates.measuring_letter_number' => 'Nomor Surat Ukur',
                'certificates.measuring_letter_date' => 'Tanggal Surat Ukur',
                'certificates.measuring_letter_status' => 'Status Surat Ukur',
                'certificates.field_map_status' => 'Status Peta Bidang',
                'certificates.wide' => 'Luas (M2)',
                'certificate_files.file' => 'File',
                'urban_villages.name' => 'Kelurahan',
                'sub_districts.name' => 'Kecamatan',
                'districts.name' => 'Kabupaten / Kotamadya',
                'provinces.name' => 'Provinsi',
                'holders.name' => 'Pemegang Hak',
                'users.name' => 'Pembuat',
            ])
            ->addColumns([
                'name' => 'Nama Sertifikat',
                'area_code' => 'Kode Wilayah',
                'certificate_type' => 'Tipe Sertifikat',
                'certificate_number' => 'Nomor Sertifikat',
                'certificate_print_number' => 'Nomor Cetak Sertifikat',
                'certificate_bookkeeping_date' => 'Tanggal Pembukuan',
                'certificate_publishing_date' => 'Tanggal Penerbitan',
                'certificate_final_date' => 'Tanggal Akhir / Perpanjangan',
                'nib' => 'NIB',
                'origin_right_category' => 'Kategori Asal Hak',
                'base_registration_decree_number' => 'Surat Keputusan',
                'base_registration_date' => 'Tanggal Dasar Pendaftaran',
                'measuring_letter_number' => 'Nomor Surat Ukur',
                'measuring_letter_date' => 'Tanggal Surat Ukur',
                'measuring_letter_status' => 'Status Surat Ukur',
                'field_map_status' => 'Status Peta Bidang',
                'wide' => 'Luas (M2)',
                'certificate_file' => 'File',
                'urban_village_name' => 'Kelurahan',
                'sub_district_name' => 'Kecamatan',
                'district_name' => 'Kabupaten / Kotamadya',
                'province_name' => 'Provinsi',
                'holder_name' => 'Pemegang Hak',
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
            [],
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
            [],
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
        $query = Certificate::orderBy('certificates.name')
            ->leftJoin('users', 'users.id', '=', 'certificates.created_by')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->limit(10);
        if (!is_null($request)) {
            $query = $query->whereRaw(
                'lower(certificates.name) like "%?%"',
                Str::lower(trim($request->query('q') ?? ''))
            );
        }
        return $query->get();
    }
}
