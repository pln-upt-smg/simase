<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetSubmissionImage extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    public string $image;

    protected $fillable = ['asset_submission_id', 'image'];

    public function assetSubmission(): BelongsTo
    {
        return $this->belongsTo(AssetSubmission::class, 'asset_submission_id');
    }
}
