<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateFile extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    public string $file;

    protected $fillable = ['certificate_id', 'file'];

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'certificate_id');
    }
}
