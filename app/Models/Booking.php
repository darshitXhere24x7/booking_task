<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id', 'customer_name', 'customer_email', 'booking_date',
        'booking_type', 'booking_slot', 'booking_from', 'booking_to'
    ];

    protected $casts = [
        'booking_date' => 'date',
        'booking_from' => 'datetime:H:i',
        'booking_to' => 'datetime:H:i',
    ];
}
