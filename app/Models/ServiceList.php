<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceList extends Model
{
    protected $table = 'VISMS_ServiceList';

    protected $fillable = [

    ];

    protected $casts = [
        'RegDate' => 'datetime',
    ];
}
