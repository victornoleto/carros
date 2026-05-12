<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'filters',
        'active',
    ];

    protected $casts = [
        'filters' => 'array',
        'active' => 'boolean',
    ];
}
