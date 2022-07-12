<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetSubmissionImage extends Model
{
    use Fluent, HasFactory, SoftDeletes;

    #[BelongsTo('asset_submission_id')]
    public AssetSubmission $assetSubmission;

    public string $image;

    protected $fillable = ['asset_submission_id', 'image'];
}
