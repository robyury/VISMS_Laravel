<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllowedServerList extends Model
{
    protected $table = 'VISMS_AllowedServerList';

    protected $fillable = [

    ];

    protected $casts = [
        'RegDate' => 'datetime',
    ];
}
