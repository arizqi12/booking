<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class McService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'price',
        'description',
        'included_services',
        'is_active',
    ];

    protected $casts = [
        'price' => 'float',
        'is_active' => 'boolean',
        'included_services' => 'array', // Cast kolom JSON menjadi array PHP
    ];

    // Opsional: scopes untuk mempermudah query
    public function scopeIndividual($query)
    {
        return $query->where('type', 'individual');
    }

    public function scopePackage($query)
    {
        return $query->where('type', 'package');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}