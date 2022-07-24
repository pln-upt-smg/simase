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

    protected $fillable = ['asset_type_id', 'custom_attributes'];

    protected $casts = [
        'custom_attributes' => 'array',
    ];

    public function assetType(): BelongsTo
    {
        return $this->belongsTo(AssetType::class, 'asset_type_id');
    }
}
