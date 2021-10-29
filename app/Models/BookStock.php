<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookStock extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    #[BelongsTo]
    public Area $area;

    #[BelongsTo]
    public Period $period;

    #[BelongsTo]
    public Material $material;

    public string $batch;
    public int $quantity, $plnt, $sloc, $qualinsp;
    public float $unrestricted;

    protected $fillable = [
        'area_id',
        'period_id',
        'material_id',
        'batch',
        'quantity',
        'plnt',
        'sloc',
        'qualinsp',
        'unrestricted'
    ];
}
