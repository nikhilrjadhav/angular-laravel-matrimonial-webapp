<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileLifestyle extends Model
{
    protected $table = 'profile_lifestyle';

    protected $fillable = [
        'user_id',
        'smoking',
        'drinking',
        'diet'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
