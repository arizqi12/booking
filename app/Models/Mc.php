<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mc extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'rates_per_hour',
        'min_duration_hours',
        'contact_phone',
        'profile_picture_url',
    ];

    protected $casts = [
        'rates_per_hour' => 'float',
        'min_duration_hours' => 'float',
    ];

    // --- RELASI ---

    /**
     * Get the user that owns the MC profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the portfolios for the MC.
     */
    public function portfolios()
    {
        return $this->hasMany(McPortfolio::class);
    }

    /**
     * Get the schedules for the MC.
     */
    public function schedules()
    {
        return $this->hasMany(McSchedule::class);
    }

    /**
     * Get the bookings for the MC.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the reviews for the MC.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}