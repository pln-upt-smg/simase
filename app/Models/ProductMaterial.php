<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductMaterial extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    #[BelongsTo]
    public Product $product;

    #[BelongsTo]
    public Material $material;

    public string $code, $description, $materialUom;
    public int $materialQuantity, $productQuantity;

    protected $fillable = [
        'product_id',
        'material_id',
        'material_uom',
        'material_quantity',
        'product_quantity'
    ];
}
