<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetLossDamageImage extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    #[BelongsTo('asset_loss_damage_id')]
    public AssetLossDamage $assetLossDamage;

    public string $image;

    protected $fillable = ['asset_loss_damage_id', 'image'];
}
