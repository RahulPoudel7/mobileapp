<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\carts_items;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'quantity',
        'subtotal',
        'gift_wrapping_fee',     // ← NEW: Added for gift wrapping charges
        'personal_note_fee',     // ← NEW: Added for personal note charges
        'personal_note_text',    // ← NEW: Added for storing note text
        'delivery_charge',
        'total_amount',
        'distance_km',
        'payment_method',
        'recipient_name',
        'recipient_phone',
        'delivery_address',
        'delivery_lat',
        'delivery_lng',
        'delivery_date',
        'status',
        'delivered_at',
        'has_personal_note',
        'has_gift_wrapping',
        'payment_status',
        'transaction_uuid'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gift()
    {
        return $this->belongsTo(Gift::class);
    }
    
    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function getPaymentMethodDisplayName()
    {
        return match($this->payment_method) {
            'cod' => 'Cash on Delivery',
            'esewa' => 'eSewa',
        };
    } 

    public function items()
    {
        return $this->hasMany(carts_items::class, 'order_id');
    }
}
