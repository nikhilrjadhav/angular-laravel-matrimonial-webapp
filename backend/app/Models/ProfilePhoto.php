<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilePhoto extends Model
{
    protected $table = 'profile_photos';

    protected $fillable = [
        'user_id',
        'photo_path',
        'is_primary'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
