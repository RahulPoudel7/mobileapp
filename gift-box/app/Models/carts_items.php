<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class carts_items extends Model
{
    protected $fillable = [
        'order_id',
        'gift_id',
        'quantity',
        'price'
    ];

    public function order()
{
    return $this->belongsTo(Order::class);
}

public function gift()
{
    return $this->belongsTo(Gift::class);
}

}
