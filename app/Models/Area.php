<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\Relation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Area extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    #[Relation]
    public Collection $bookStocks;

    public string $sloc, $name, $group;

    protected $fillable = [
        'sloc',
        'name',
        'group'
    ];

    public function bookStocks(): HasMany
    {
        return $this->hasMany(BookStock::class, 'sloc', 'sloc');
    }
}
