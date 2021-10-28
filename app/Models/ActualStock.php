<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActualStock extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    #[BelongsTo]
    public Material $material;

    public string $batch;
    public int $plnt, $sloc, $qualinsp;
    public float $unrestricted;

    protected $fillable = [
        'material_id',
        'batch',
        'plnt',
        'sloc',
        'qualinsp',
        'unrestricted'
    ];
}
