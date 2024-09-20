<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseLog extends Model
{
    protected $table = 'VISMS_PurchaseLog';
    const CREATED_AT = 'RegDate';
    const UPDATED_AT = 'UpdDate';

    protected $fillable = [
        'ServiceCode',
        'OrderID',
        'ProductNo',
        'PaymentType',
        'PaymentRuleID',
        'TotalPrice',
        'OrderAmmount',
        'oid',
        'strNexonID',
        'IPAddress',
        'IsGift',
        'Receiver_oid',
        'Receiver_strNexonID',
    ];

    protected $casts = [
        'RegDate' => 'datetime',
        'UpdDate' => 'datetime',
    ];
}
