<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'mobile',
        'email',
        'gender',
        'password',
        'profile_status',
        'is_verified',
        'is_active',
        'last_login'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function family()
    {
        return $this->hasOne(ProfileFamily::class);
    }

    public function lifestyle()
    {
        return $this->hasOne(ProfileLifestyle::class);
    }

    public function partnerPreference()
    {
        return $this->hasOne(ProfilePartnerPreference::class);
    }

    public function photos()
    {
        return $this->hasMany(ProfilePhoto::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
