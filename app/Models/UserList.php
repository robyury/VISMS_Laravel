<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserList extends Model
{
    const UPDATED_AT = 'UpdDate';
    protected $table = 'VISMS_UserList';

    protected $fillable = [
        'RealBalance',
        'BonusBalance',
    ];

    protected $casts = [
        'RegDate' => 'datetime',
        'UpdDate' => 'datetime',
    ];

}
