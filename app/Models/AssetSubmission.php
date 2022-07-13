<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Helpers\HasPriority;

class AssetSubmission extends Model
{
    use Fluent, HasFactory, HasPriority, SoftDeletes;

    public string $note;
    public int $quantity, $priority;

    protected $fillable = [
        'asset_id',
        'created_by',
        'note',
        'quantity',
        'priority',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assetSubmissionImages(): HasMany
    {
        return $this->hasMany(
            AssetSubmissionImage::class,
            'asset_submission_id'
        );
    }
}
