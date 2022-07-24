<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    public string $name;

    protected $fillable = ['name'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'division_id');
    }

    public static function renev(): ?self
    {
        return self::whereNull('deleted_at')->find(1);
    }

    public static function construction(): ?self
    {
        return self::whereNull('deleted_at')->find(2);
    }

    public static function kku(): ?self
    {
        return self::whereNull('deleted_at')->find(3);
    }

    public function isRenev(): bool
    {
        return $this->id === 1;
    }

    public function isConstruction(): bool
    {
        return $this->id === 2;
    }

    public function isKKU(): bool
    {
        return $this->id === 3;
    }
}
