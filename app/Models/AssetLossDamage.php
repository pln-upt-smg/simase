<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\BelongsTo;
use Based\Fluent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use App\Models\Helpers\HasPriority;

class AssetLossDamage extends Model
{
    use Fluent, HasFactory, HasPriority, SoftDeletes;

    #[BelongsTo('asset_id')]
    public Asset $asset;

    #[BelongsTo('created_by')]
    public User $user;

    #[HasMany(AssetLossDamageImage::class, 'asset_loss_damage_id')]
    public Collection $images;

    public string $note;
    public int $priority;

    protected $fillable = ['asset_id', 'created_by', 'note', 'priority'];
}
