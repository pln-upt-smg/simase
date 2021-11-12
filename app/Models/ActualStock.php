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
    public SubArea $subArea;

    #[BelongsTo]
    public Material $material;

    #[BelongsTo]
    public User $user;

    public string $batch;
    public float $quantity;

    protected $fillable = [
        'sub_area_id',
        'material_id',
        'user_id',
        'batch',
        'quantity'
    ];
}
