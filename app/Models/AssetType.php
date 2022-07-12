<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\BelongsTo;
use Based\Fluent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class AssetType extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    #[BelongsTo('created_by')]
    public User $user;

    #[HasMany(Asset::class, 'asset_type_id')]
    public Collection $assets;

    public string $name, $uom;

    protected $fillable = [
        'created_by',
        'name',
        'uom'
    ];
}
