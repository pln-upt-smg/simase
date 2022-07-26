<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Certificate extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    public string $area_code,
        $certificate_type,
        $certificate_number,
        $certificate_print_number,
        $nib,
        $origin_right_category,
        $decree,
        $measuring_letter_certificate_number;
    public bool $measuring_letter_status, $field_map_status;
    public float $wide;

    protected $fillable = [
        'urban_village_id',
        'sub_district_id',
        'district_id',
        'province_id',
        'holder_id',
        'created_by',
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
    ];

    public function urbanVillage(): BelongsTo
    {
        return $this->belongsTo(UrbanVillage::class, 'urban_village_id');
    }

    public function subDistrict(): BelongsTo
    {
        return $this->belongsTo(SubDistrict::class, 'sub_district_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function holder(): BelongsTo
    {
        return $this->belongsTo(Holder::class, 'holder_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function certificateFile(): HasOne
    {
        return $this->hasOne(CertificateFile::class, 'certificate_id');
    }
}
