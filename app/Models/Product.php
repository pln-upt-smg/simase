<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\BelongsTo;
use Based\Fluent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Product extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    #[BelongsTo]
    public Period $period;

    #[HasMany(ProductMaterial::class)]
    public Collection $productMaterials;

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

    public function convertAsActualStock(Request $request): void
    {
        $productMaterials = $this->load('productMaterials')->productMaterials;
        foreach ($productMaterials as $productMaterial) {
            for($i = 0; $i < (int)$request->quantity; $i++) {
                ActualStock::create([
                    'area_id' => (int)$request->area,
                    'material_id' => $productMaterial->load('material')->material->id,
                    'user_id' => auth()->user()?->id ?? 0,
                    'batch' => Str::upper($request->batch_code),
                    'quantity' => $productMaterial->material_quantity
                ]);
            }
        }
    }
}
