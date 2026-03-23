<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilePartnerPreference extends Model
{
    protected $table = 'profile_partner_preferences';

    protected $fillable = [
        'user_id',
        'age_min',
        'age_max',
        'religion',
        'samaj_id',
        'education',
        'occupation'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function samaj()
    {
        return $this->belongsTo(Samaj::class);
    }
}
