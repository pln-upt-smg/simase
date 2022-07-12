<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    public string $code, $name;
    public float $lat, $lon;

    protected $fillable = [
        'area_type_id',
        'created_by',
        'code',
        'name',
        'lat',
        'lon',
    ];

    public function areaType()
    {
        return $this->belongsTo(AreaType::class, 'area_type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'area_id');
    }
}
