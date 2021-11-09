<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\BelongsTo;
use Based\Fluent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    #[BelongsTo]
    public Period $period;

    #[HasOne]
    public Batch $batch;

    public string $code, $description, $uom, $mtyp, $crcy;
    public int $price, $per;

    protected $fillable = [
        'period_id',
        'code',
        'description',
        'uom',
        'mtyp',
        'crcy',
        'price',
        'per'
    ];
}
