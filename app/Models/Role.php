<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Role extends Model
{
    use Fluent, HasFactory;

    #[HasMany(User::class, 'role_id')]
    public Collection $users;

    public string $name;

    protected $fillable = [
        'name'
    ];

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
