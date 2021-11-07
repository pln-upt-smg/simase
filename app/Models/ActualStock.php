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

    #[BelongsTo]
    public User $creator;

    public string $batch;
    public float $quantity;

    protected $fillable = [
        'material_id',
        'user_id',
        'batch',
        'quantity'
    ];
}
