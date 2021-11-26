<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Area extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    #[HasMany(SubArea::class)]
    public Collection $subAreas;

    #[HasMany(BookStock::class)]
    public Collection $bookStocks;

    public string $sloc, $name;
    public bool $is_batch_required;

    protected $fillable = [
        'sloc',
        'name',
        'is_batch_required'
    ];
}
