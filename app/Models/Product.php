<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\BelongsTo;
use Based\Fluent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Collection\Collection;

class Product extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    #[BelongsTo]
    public Area $area;

    #[BelongsTo]
    public Period $period;

    #[HasMany(ProductMaterials::class)]
    public Collection $materials;

    public string $code, $description, $uom;
    public int $quantity;

    protected $fillable = [
        'area_id',
        'period_id',
        'code',
        'description',
        'uom',
        'quantity'
    ];
}
