<?php

namespace App\Imports;

use Illuminate\Contracts\Queue\{ShouldBeUnique, ShouldQueue};
use Illuminate\Support\Collection;
use App\Imports\Contracts\WithDefaultEvents;
use App\Imports\Helpers\{
    HasBatchSize,
    HasChunkSize,
    HasDefaultEvents,
    HasDefaultSheet,
    HasImporter,
    HasUrbanVillageResolver,
    HasSubDistrictResolver,
    HasDistrictResolver,
    HasProvinceResolver,
    HasHolderResolver,
    HasOptionalityResolver
};
use App\Models\{Certificate, User};
use Maatwebsite\Excel\Concerns\{
    Importable,
    SkipsEmptyRows,
    SkipsErrors,
    SkipsFailures,
    SkipsOnError,
    SkipsOnFailure,
    ToCollection,
    WithBatchInserts,
    WithChunkReading,
    WithEvents,
    WithHeadingRow,
    WithMultipleSheets,
    WithUpserts,
    WithValidation
};
use Carbon\Carbon;

class CertificateImport implements
    ToCollection,
    SkipsOnFailure,
    SkipsOnError,
    SkipsEmptyRows,
    WithHeadingRow,
    WithMultipleSheets,
    WithChunkReading,
    WithBatchInserts,
    WithUpserts,
    WithEvents,
    WithDefaultEvents,
    WithValidation,
    ShouldQueue,
    ShouldBeUnique
{
    use Importable,
        SkipsFailures,
        SkipsErrors,
        HasDefaultSheet,
        HasDefaultEvents,
        HasImporter,
        HasChunkSize,
        HasBatchSize,
        HasUrbanVillageResolver,
        HasSubDistrictResolver,
        HasDistrictResolver,
        HasProvinceResolver,
        HasHolderResolver,
        HasOptionalityResolver;

    public function __construct(?User $user)
    {
        if (is_null($user)) {
            $this->userId = 0;
        } else {
            $this->userId = $user->id;
        }
    }

    public function rules(): array
    {
        return [
            'namaasettanah' => ['required', 'string', 'max:255'],
            'kelurahan' => ['required', 'string', 'max:255'],
            'kecamatan' => ['required', 'string', 'max:255'],
            'kabupatenkotamadya' => ['required', 'string', 'max:255'],
            'provinsi' => ['required', 'string', 'max:255'],
            'kodewilayah' => ['required', 'string', 'max:255'],
            'nocetaksertifikat' => ['required', 'string', 'max:255'],
            'tipesertifikat' => ['required', 'string', 'max:255'],
            'nosertifikat' => ['required', 'string', 'max:255'],
            'nib' => ['required', 'string', 'max:255'],
            'kategoriasalhak' => ['required', 'string', 'max:255'],
            'suratkeputusan' => ['required', 'string', 'max:255'],
            'tanggaldasarpendaftaran' => ['required', 'date_format:d/m/Y'],
            'nosuratukur' => ['required', 'string', 'max:255'],
            'tanggalsuratukur' => ['required', 'date_format:d/m/Y'],
            'statussuratukur' => ['required', 'string', 'max:255'],
            'statuspetabidang' => ['required', 'string', 'max:255'],
            'luas(m2)' => ['required', 'numeric', 'min:0'],
            'tanggalpembukuan' => ['required', 'date_format:d/m/Y'],
            'tanggalpenerbitan' => ['required', 'date_format:d/m/Y'],
            'tanggalakhir' => ['required', 'date_format:d/m/Y'],
            'namapemeganghak' => ['required', 'string', 'max:255'],
        ];
    }

    public function uniqueBy()
    {
        return ['nosertifikat'];
    }

    public function collection(Collection $collection): void
    {
        foreach ($collection as $row) {
            $this->replace($row->toArray());
        }
    }

    public function name(): string
    {
        return 'Sertifikat';
    }

    public function replace(array $row): void
    {
        $urbanVillageId = $this->resolveUrbanVillageId($row['kelurahan']);
        $subDistrictId = $this->resolveSubDistrictId($row['kecamatan']);
        $districtId = $this->resolveUrbanVillageId($row['kabupatenkotamadya']);
        $provinceId = $this->resolveProvinceId($row['provinsi']);
        $holderId = $this->resolveHolderId($row['namapemeganghak']);
        if (
            $urbanVillageId === 0 ||
            $subDistrictId === 0 ||
            $districtId === 0 ||
            $provinceId === 0 ||
            $holderId === 0 ||
            $this->userId === 0
        ) {
            return;
        }
        Certificate::updateOrCreate([
            'urban_village_id' => $urbanVillageId,
            'sub_district_id' => $subDistrictId,
            'district_id' => $districtId,
            'province_id' => $provinceId,
            'holder_id' => $provinceId,
            'created_by' => $this->userId,
            'name' => trim($row['namaasettanah']),
            'area_code' => trim($row['kodewilayah']),
            'certificate_type' => trim($row['tipesertifikat']),
            'certificate_number' => trim($row['nosertifikat']),
            'certificate_print_number' => trim($row['nocetaksertifikat']),
            'certificate_bookkeeping_date' => Carbon::createFromFormat(
                'd/m/Y',
                trim($row['tanggalpembukuan'])
            ),
            'certificate_publishing_date' => Carbon::createFromFormat(
                'd/m/Y',
                trim($row['tanggalpenerbitan'])
            ),
            'certificate_final_date' => Carbon::createFromFormat(
                'd/m/Y',
                trim($row['tanggalakhir'])
            ),
            'nib' => trim($row['nib']),
            'origin_right_category' => trim($row['kategoriasalhak']),
            'base_registration_decree_number' => trim($row['suratkeputusan']),
            'base_registration_date' => Carbon::createFromFormat(
                'd/m/Y',
                trim($row['tanggaldasarpendaftaran'])
            ),
            'measuring_letter_number' => trim($row['nosuratukur']),
            'measuring_letter_date' => Carbon::createFromFormat(
                'd/m/Y',
                trim($row['tanggalsuratukur'])
            ),
            'measuring_letter_status' => $this->resolveOptionality(
                $row['statussuratukur']
            ),
            'field_map_status' => $this->resolveOptionality(
                $row['statuspetabidang']
            ),
            'wide' => $row['luas(m2)'],
        ]);
    }

    public function overwrite(): void
    {
        Certificate::leftJoin(
            'users',
            'users.id',
            '=',
            'certificates.created_by'
        )
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->whereNull('deleted_at')
            ->delete();
    }
}
