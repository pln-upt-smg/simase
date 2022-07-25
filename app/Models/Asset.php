<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    public string $techidentno, $name;
    public int $quantity;

    protected $fillable = [
        'asset_type_id',
        'area_id',
        'created_by',
        'techidentno',
        'name',
        'quantity',
    ];

    public function assetType(): BelongsTo
    {
        return $this->belongsTo(AssetType::class, 'asset_type_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assetSubmissions(): HasMany
    {
        return $this->hasMany(AssetSubmission::class, 'asset_id');
    }

    public function assetLossDamages(): HasMany
    {
        return $this->hasMany(AssetLossDamage::class, 'asset_id');
    }
}
