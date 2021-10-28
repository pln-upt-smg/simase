<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Batch extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    #[BelongsTo]
    public Material $material;

    public string $code;

    protected $fillable = [
        'material_id',
        'code'
    ];
}