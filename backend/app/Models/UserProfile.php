<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $table = 'user_profiles';

    protected $fillable = [
        'user_id',
        'birth_date',
        'birth_time',
        'height',
        'weight',
        'blood_group',
        'complexion',
        'body_type',
        'special_case',
        'religion',
        'samaj',
        'caste',
        'education',
        'occupation',
        'country_id',
        'state_id',
        'city_id',
        'about_me',
        'profile_completed'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function samaj()
    {
        return $this->belongsTo(Samaj::class, 'samaj');
    }
}
