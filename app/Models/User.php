<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Fluent, HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable, SoftDeletes, SerializesModels;

    public string $name, $nip, $password;
    public ?string $phone;

    protected $fillable = [
        'role_id',
        'name',
        'phone',
        'nip',
        'password'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret'
    ];

    protected $appends = [
        'profile_photo_url'
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function areaTypes(): HasMany
    {
        return $this->hasMany(AreaType::class, 'created_by');
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class, 'created_by');
    }

    public function assetTypes(): HasMany
    {
        return $this->hasMany(AssetType::class, 'created_by');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'created_by');
    }

    public function assetSubmissions(): HasMany
    {
        return $this->hasMany(AssetSubmission::class, 'created_by');
    }

    public function assetLossDamages(): HasMany
    {
        return $this->hasMany(AssetLossDamage::class, 'created_by');
    }

    public function setRoleAttribute(Role|int $role): void
    {
        $this->role_id = $role instanceof Role ? $role->id : $role;
    }
}
