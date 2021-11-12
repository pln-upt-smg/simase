<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\BelongsTo;
use Based\Fluent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class SubArea extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    #[BelongsTo]
    public Area $area;

    #[HasMany(ActualStock::class)]
    public Collection $actualStocks;

    public string $name;

    protected $fillable = [
        'area_id',
        'name'
    ];
}
