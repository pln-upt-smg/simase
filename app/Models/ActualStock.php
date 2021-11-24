<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\BelongsTo;
use Based\Fluent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class ActualStock extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    #[BelongsTo]
    public SubArea $subArea;

    #[BelongsTo]
    public Material $material;

    #[BelongsTo]
    public User $user;

	#[HasMany(ProductBreakdown::class)]
	public Collection $productBreakdowns;

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
