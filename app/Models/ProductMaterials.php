<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductMaterials extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    #[BelongsTo]
    public Product $product;

    #[BelongsTo]
    public Material $material;

    public string $code, $description, $uom;
    public int $quantity;

    protected $fillable = [
        'product_id',
        'material_id',
        'uom',
        'quantity'
    ];
}
