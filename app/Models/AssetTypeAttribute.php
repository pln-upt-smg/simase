<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetTypeAttribute extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    protected $fillable = ['asset_type_id', 'attributes'];

    protected $casts = [
        'attributes' => 'array',
    ];

    public function assetType(): BelongsTo
    {
        return $this->belongsTo(AssetType::class, 'asset_type_id');
    }

    public function setAttributesAttribute(array $attributes): void
    {
        $this->attributes = json_encode($attributes);
    }

    public function getAttributesAttribute(): array
    {
        if (empty($this->attributes)) {
            return [];
        }
        return json_decode($this->attributes);
    }
}
