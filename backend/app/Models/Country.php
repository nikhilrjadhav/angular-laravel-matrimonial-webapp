<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name','iso_code','latitude','longitude'];

    public function states()
    {
        return $this->hasMany(State::class);
    }
}
