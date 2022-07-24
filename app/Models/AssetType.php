<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AssetType extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    public string $name;

    protected $fillable = ['created_by', 'name'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'asset_type_id');
    }

    public function assetTypeAttribute(): HasOne
    {
        return $this->hasOne(AssetTypeAttribute::class, 'asset_type_id');
    }
}
