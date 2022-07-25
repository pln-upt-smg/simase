<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    public string $funcloc, $name;
    public float $lat, $lon;

    protected $fillable = [
        'area_type_id',
        'created_by',
        'funcloc',
        'name',
        'lat',
        'lon',
    ];

    public function areaType(): BelongsTo
    {
        return $this->belongsTo(AreaType::class, 'area_type_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'area_id');
    }
}
