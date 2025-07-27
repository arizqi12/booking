<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class McSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'mc_id',
        'date',
        'start_time',
        'end_time',
        'is_available',
    ];

    protected $casts = [
        'date' => 'date',
        'is_available' => 'boolean',
    ];

    /**
     * Get the MC that owns the schedule.
     */
    public function mc()
    {
        return $this->belongsTo(Mc::class);
    }

    /**
     * Get the bookings associated with this schedule.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'schedule_id');
    }
}