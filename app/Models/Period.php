<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Queue\SerializesModels;

class Period extends Model
{
    use Fluent, HasFactory, SoftDeletes, SerializesModels;

    public string $name;

    protected $fillable = [
        'name'
    ];
}
