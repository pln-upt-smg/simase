<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AreaType extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    public string $name;

    protected $fillable = ['created_by', 'name'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function areas()
    {
        return $this->hasMany(Area::class, 'area_type_id');
    }
}
