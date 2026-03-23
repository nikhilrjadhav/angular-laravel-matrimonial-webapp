<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileFamily extends Model
{
    protected $table = 'profile_family';

    protected $fillable = [
        'user_id',
        'father_name',
        'mother_name',
        'father_occupation',
        'mother_occupation',
        'brothers',
        'sisters',
        'married_brothers',
        'married_sisters',
        'family_type',
        'family_country_id',
        'family_state_id',
        'family_city_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'family_country_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'family_state_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'family_city_id');
    }
}
