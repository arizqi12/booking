<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class McPortfolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'mc_id',
        'title',
        'description',
        'media_path',
        'media_type',
    ];

    /**
     * Get the MC that owns the portfolio.
     */
    public function mc()
    {
        return $this->belongsTo(Mc::class);
    }
}