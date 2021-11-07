<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Queue\SerializesModels;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Fluent, HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable, SoftDeletes, SerializesModels;

    #[BelongsTo]
    public Role $role;

    public string $name, $phone, $nip, $password;

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

    public function setRoleAttribute(Role|int $role): void
    {
        $this->role_id = $role instanceof Role ? $role->id : $role;
    }
}
