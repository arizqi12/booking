<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'amount',
        'payment_type',
        'midtrans_transaction_id',
        'midtrans_status',
        'payment_method',
        'transaction_time',
        'raw_response',
    ];

    protected $casts = [
        'amount' => 'float',
        'transaction_time' => 'datetime',
        'raw_response' => 'array', // Cast JSON column to array
    ];

    /**
     * Get the booking that owns the payment.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}