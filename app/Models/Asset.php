<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\BelongsTo;
use Based\Fluent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Asset extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    #[BelongsTo('asset_type_id')]
    public AssetType $assetType;

    #[BelongsTo('area_id')]
    public Area $area;

    #[BelongsTo('created_by')]
    public User $user;

    #[HasMany(AssetSubmission::class, 'asset_id')]
    public Collection $assetSubmissions;

    #[HasMany(AssetLossDamage::class, 'asset_id')]
    public Collection $assetLossDamages;

    public string $name;
    public int $quantity;

    protected $fillable = [
        'asset_type_id',
        'area_id',
        'created_by',
        'name',
        'quantity',
    ];
}
