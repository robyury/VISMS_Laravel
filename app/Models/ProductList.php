<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductList extends Model
{
    protected $table = 'VISMS_ProductList';

    protected $fillable = [

    ];

    protected $casts = [
        'RegDate' => 'datetime',
    ];
}
