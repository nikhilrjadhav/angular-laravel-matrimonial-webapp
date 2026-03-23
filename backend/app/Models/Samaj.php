<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Samaj extends Model
{
    protected $table = 'samaj';

    protected $fillable = [
        'name',
        'page_title',
        'page_description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function profiles()
    {
        return $this->hasMany(UserProfile::class, 'samaj');
    }

    public function partnerPreferences()
    {
        return $this->hasMany(ProfilePartnerPreference::class, 'samaj_id');
    }
}
