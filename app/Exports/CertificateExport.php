<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping};
use App\Notifications\DataExported;
use App\Services\CertificateService;

class CertificateExport implements FromCollection, WithHeadings, WithMapping
{
    private CertificateService $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    public function headings(): array
    {
        return [
            'Nama Aset Tanah',
            'Kelurahan',
            'Kecamatan',
            'Kabupaten / Kotamadya',
            'Provinsi',
            'Kode Wilayah',
            'No. Cetak Sertifikat',
            'Tipe Sertifikat',
            'No. Sertifikat',
            'NIB',
            'Kategori Asal Hak',
            'Surat Keputusan',
            'Tanggal Dasar Pendaftaran',
            'No. Surat Ukur',
            'Tanggal Surat Ukur',
            'Status Surat Ukur',
            'Status Peta Bidang',
            'Luas (M2)',
            'Tanggal Pembukuan Sertifikat',
            'Tanggal Penerbitan Sertifikat',
            'Tanggal Akhir / Perpanjangan Sertifikat',
            'Nama Pemegang Hak',
            'File Sertifikat',
        ];
    }

    public function map($row): array
    {
        return [
            trim($row->name),
            trim($row->urbanVillage->name),
            trim($row->subDistrict->name),
            trim($row->district->name),
            trim($row->province->name),
            trim($row->area_code),
            trim($row->certificate_print_number),
            trim($row->certificate_type),
            trim($row->certificate_number),
            trim($row->nib),
            trim($row->origin_right_category),
            trim($row->base_registration_decree_number),
            $row->base_registration_date,
            trim($row->measuring_letter_number),
            trim($row->measuring_letter_date),
            $row->measuring_letter_status ? 'Ada' : 'Tidak Ada',
            $row->field_map_status ? 'Ada' : 'Tidak Ada',
            $row->wide,
            $row->certificate_bookkeeping_date,
            $row->certificate_publishing_date,
            $row->certificate_final_date,
            trim($row->holder->name),
            $row->certificateFile->file ?? null,
        ];
    }

    public function collection(): Collection
    {
        $data = $this->certificateService->collection();
        if (!is_null(auth()->user())) {
            auth()
                ->user()
                ->notify(new DataExported('Sertifikat', $data->count()));
        }
        return $data;
    }
}
