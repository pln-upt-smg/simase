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

    public string $name;

    #[HasMany(User::class)]
    public Collection $users;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
    ];

    public static function administrator(): ?self
    {
        return self::find(1);
    }

    public static function operator(): ?self
    {
        return self::find(2);
    }
}
