<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mc_id',
        'schedule_id',
        'event_date',
        'event_start_time',
        'event_end_time',
        'event_type',
        'location',
        'notes',
        'total_amount',
        'service_fee',
        'grand_total',
        'dp_required_amount',
        'paid_amount',
        'remaining_amount',
        'payment_status',
        'booking_status',
        'midtrans_last_trx_id',
        'midtrans_snap_token',
        'cancellation_reason',
    ];

    protected $casts = [
        'event_date' => 'date',
        'event_start_time' => 'datetime',
        'event_end_time' => 'datetime',
        'total_amount' => 'float',
        'service_fee' => 'float',
        'grand_total' => 'float',
        'dp_required_amount' => 'float',
        'paid_amount' => 'float',
        'remaining_amount' => 'float',
    ];

    // --- RELASI ---

    /**
     * Get the user who made the booking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the MC for whom the booking was made.
     */
    public function mc()
    {
        return $this->belongsTo(Mc::class);
    }

    /**
     * Get the schedule associated with the booking.
     */
    public function schedule()
    {
        return $this->belongsTo(McSchedule::class, 'schedule_id');
    }

    /**
     * Get the payments for the booking.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the review for the booking.
     */
    public function review()
    {
        return $this->hasOne(Review::class);
    }
}