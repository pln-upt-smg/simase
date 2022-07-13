<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetLossDamageImage extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    public string $image;

    protected $fillable = ['asset_loss_damage_id', 'image'];

    public function assetLossDamage(): BelongsTo
    {
        return $this->belongsTo(AssetLossDamage::class, 'asset_loss_damage_id');
    }
}
