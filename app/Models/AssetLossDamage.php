<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Helpers\HasPriority;

class AssetLossDamage extends Model
{
    use Fluent, HasFactory, HasPriority, SoftDeletes;

    public ?string $note;
    public int $priority;

    protected $fillable = ['asset_id', 'created_by', 'note', 'priority'];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assetLossDamageImages(): HasMany
    {
        return $this->hasMany(
            AssetLossDamageImage::class,
            'asset_loss_damage_id'
        );
    }
}
