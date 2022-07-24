<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetTransferImage extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    public string $image;

    protected $fillable = ['asset_transfer_id', 'image'];

    public function assetTransfer(): BelongsTo
    {
        return $this->belongsTo(AssetTransfer::class, 'asset_transfer_id');
    }
}
