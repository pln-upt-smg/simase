<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    public string $name;

    protected $fillable = ['name'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public static function administrator(): ?self
    {
        return self::whereNull('deleted_at')->find(1);
    }

    public static function operator(): ?self
    {
        return self::whereNull('deleted_at')->find(2);
    }

    public function isAdministrator(): bool
    {
        return $this->id === 1;
    }

    public function isOperator(): bool
    {
        return $this->id === 2;
    }
}
